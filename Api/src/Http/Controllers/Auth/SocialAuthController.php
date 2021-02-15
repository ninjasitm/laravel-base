<?php
namespace Nitm\Api\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Two\AbstractProvider;
use MadWeb\SocialAuth\Models\SocialProvider;
use MadWeb\SocialAuth\Events\SocialUserDetached;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Contracts\Factory as Socialite;
use MadWeb\SocialAuth\Exceptions\SocialUserAttachException;
use MadWeb\SocialAuth\Exceptions\SocialGetUserInfoException;
use MadWeb\SocialAuth\Controllers\SocialAuthController as BaseController;

/**
 * Class SocialAuthController.
 */
class SocialAuthController extends BaseController
{
    use \Nitm\Content\Http\Controllers\Traits\CustomControllerTrait;

    public function __construct(Guard $auth, Socialite $socialite)
    {
        $this->auth = $auth;
        $this->socialite = $socialite;
        $this->redirectTo = config('social-auth.redirect');

        $className = config('social-auth.models.user');
        $this->userModel = new $className;

        if (request()->route('social')) {
            $this->middleware(
                function ($request, $next) {
                    $class = config('nitm-api.social_auth_provider', '\\Nitm\\Api\\Auth\\SocialProviderManager');
                    $this->manager = new $class($request->route('social'));

                    return $next($request);
                }
            );
        }
        $this->middleware(config('nitm-api.social_auth_middleware'));
    }

    public function refreshToken(SocialProvdier $social)
    {
        $provider = $this->socialite->driver($social->slug);
        $account = auth()->user()->socials()->whereSocialProviderId($social->id)->first();

        if (!$account) {
            abort(404);
        }

        $this->checkToken($provider, $account);

        return $this->printSuccess($account->token);
    }

    /**
     * If there is no response from the social network, redirect the user to the social auth page
     * else make create with information from social network.
     *
     * @param  SocialProvider $social bound by "Route model binding" feature
     * @return JsonResponse
     */
    public function getAccounts()
    {
        $accounts = auth()->user()->socials()->get()->map(
            function ($account) {
                $provider = $this->socialite->driver($account->slug);
                $this->checkToken($provider, $account);
                if ($account->token && $account->token->token) {
                    return $account;
                }
                return null;
            }
        )->filter();
        return $this->printSuccess($accounts);
    }

    /**
     * If there is no response from the social network, redirect the user to the social auth page
     * else make create with information from social network.
     *
     * @param  SocialProvider $social bound by "Route model binding" feature
     * @return JsonResponse
     */
    public function getAccountCustom(SocialProvider $social)
    {
        $provider = $this->socialite->driver($social->slug);

        if (!empty($social->scopes)) {
            $social->override_scopes ? $provider->setScopes($social->scopes) : $provider->scopes($social->scopes);
        }

        $account = $this->getAccount($social);
        if ($account) {
            $this->checkToken($provider, $account);
        }

        return $this->printSuccess($account);
    }

    public function getAccount(SocialProvider $social)
    {
        $account = auth()->user()->socials()->whereSocialProviderId($social->id)->first();
        $provider = $this->socialite->driver($social->slug);

        $this->checkToken($provider, $account);
        return $account;
    }

    /**
     * Redirect callback for social network.
     *
     * @param  Request        $request
     * @param  SocialProvider $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws SocialGetUserInfoException
     * @throws SocialUserAttachException
     */
    public function callbackCustom(Request $request, SocialProvider $social)
    {
        $provider = $this->socialite->driver($social->slug);

        $socialUser = null;

        // try to get user info from social network
        try {
            $socialUser = $social->stateless ? $provider->stateless()->user() : $provider->user();
        } catch (Exception $e) {
            throw new SocialGetUserInfoException($social, $e->getMessage());
        }

        // if we have no social info for some reason
        if (!$socialUser) {
            throw new SocialGetUserInfoException(
                $social,
                trans('social-auth::messages.no_user_data', ['social' => $social->label])
            );
        }

        // if user is guest
        if (!$this->auth->check()) {
            return $this->processData($request, $social, $socialUser);
        }

        $redirect_path = $this->redirectPath();
        $user = $request->user();

        // if user already attached
        if ($user->isAttached($social->slug)) {
            throw new SocialUserAttachException(
                Response::make($social),
                $social
            );
        }

        //If someone already attached current socialProvider account
        if ($this->manager->socialUserQuery($socialUser->getId())->exists()) {
            throw new SocialUserAttachException(
                Response::make($social),
                $social
            );
        }

        $this->manager->attachUser($user, $socialUser, $request->input('offline_token'));

        return $this->printSuccess($user);
    }

    /**
     * Create social account from frontend data
     *
     * @param  Request        $request
     * @param  SocialProvider $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws SocialGetUserInfoException
     * @throws SocialUserAttachException
     */
    public function callforward(Request $request, SocialProvider $social)
    {
        $type = $request->input('type');
        $provider = $this->socialite->driver($type);
        $social->stateless = true;

        $socialUser = null;
        $token = $this->getOfflineToken($provider, $social, $request->input('code'));
        // try to get user info from social network
        try {
            $socialUser = $provider->userFromToken(Arr::get($token, 'access_token'));
        } catch (Exception $e) {
            $error = new SocialGetUserInfoException($social, $e->getMessage());
            \Log::error($e);
            \Log::error($error);
            throw $error;
        }

        // if we have no social info for some reason
        if (!$socialUser) {
            \Log::error("There's no social account information for the user");
            $error = new SocialGetUserInfoException(
                $social,
                trans('social-auth::messages.no_user_data', ['social' => $social->label])
            );
            \Log::error($error);
            throw $error;
        }

        // if user is guest
        if (!auth()->check()) {
            \Log::info("User attempting to connect is not logged in");
            return $this->processDataCustom($request, $social, $socialUser);
        }

        $user = $request->user();

        // if user already attached
        if ($user->isAttached($type)) {
            \Log::info("User is already attached for this social network");
            return $this->printSuccess($this->getAccount($social));
        }

        //If someone already attached current socialProvider account
        if ($this->manager->socialUserQuery($socialUser->getId())->exists()) {
            \Log::error("This account is already connected to WeThrive!");
            $error = new SocialUserAttachException(
                Response::make($social),
                $social
            );
            \Log::error($error);
            throw $error;
        }
        $this->manager->attachUser(
            $user,
            $socialUser,
            Arr::get(
                $token,
                'refresh_token',
                Arr::get($token, 'offline_token')
            )
        );

        return $this->printSuccess($this->getAccount($social));
    }

    /**
     * Detaches social account for user.
     *
     * @param  Request        $request
     * @param  SocialProvider $social
     * @return array
     * @throws SocialUserAttachException
     */
    public function detachAccountCustom(Request $request, SocialProvider $social)
    {
        /**
 * @var \MadWeb\SocialAuth\Contracts\SocialAuthenticatable $user
*/
        $user = $request->user();
        // $userSocials = $user->socials();
        $socials = auth()->user()->socials();

        // if ($userSocials->count() === 1 and empty($user->{$user->getEmailField()})) {
        //     throw new SocialUserAttachException(
        //     trans('social-auth::messages.detach_error_last'),
        //     $social
        //     );
        // }

        // $result = $userSocials->detach($social->id);
        // if($result) {
        // Need to delete the remoe connection first
        $this->deleteToken($social);
        $result = $socials->detach($social->id);
        // }

        event(new SocialUserDetached($user, $social, $result));

        return $this->printSuccess($result);
    }

    /**
     * Process user using data from social network.
     *
     * @param  Request        $request
     * @param  SocialProvider $social
     * @param  SocialUser     $socialUser
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function processDataCustom(Request $request, SocialProvider $social, SocialUser $socialUser)
    {
        //Checks by socialProvider identifier if user exists
        $existingUser = $this->manager->getUserByKey($socialUser->getId());

        //Checks if user exists with current socialProvider identifier, auth if does
        if ($existingUser) {
            return $this->printSuccess($this->getAccount($social));
        }

        //Checks if socialProvider email exists
        if ($social_user_email = $socialUser->getEmail()) {
            //Checks if account exists with socialProvider email, auth and attach current socialProvider if does
            $existingUser = $this->userModel->where('email', $social_user_email)->first();
            if ($existingUser) {
                // $this->login($existingUser);

                $this->manager->attachUser($request->user(), $socialUser, $request->input('offline_token'));

                return $this->printSuccess($this->getAccount($social));
            }
        }

        //If account for current socialProvider data doesn't exist - create new one
        // $newUser = $this->manager->createNewUser($this->userModel, $social, $socialUser);

        return $this->printSuccess($this->getAccount($social));
    }

    /**
     * Refresh User's Google OAuth2 Access Token
     *
     * @param  [type] $account [description]
     * @return [type]         [description]
     */
    protected function refreshGoogleToken(SocialProvider $account)
    {
        $config = \Config::get('services.google');

        // Config
        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];

        // User
        // $token = $account->token->token;
        $refreshToken = $account->token->offline_token;
        $expiresIn = $account->token->expires_in;

        // If current date exceeds expired date request new access token
        if (!$expiresIn || $expiresIn && Carbon::now()->greaterThan(Carbon::parse($expiresIn)) && !empty($refreshToken)) {
            // Set Client
            $client = new Google_Client;
            if (!$account->token->offline_token) {
                $client->revokeToken($account->token->token);
                return false;
            }
            $client->setClientId($client_id);
            $client->setClientSecret($client_secret);
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');

            $result = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (Arr::get($result, 'error') == 'invalid_grant') {
                $client->revokeToken($account->token->token);
                $account->token->delete();
                $account->token = null;
                return false;
            }
            return $result;
        }

        return false;
    }

    /**
     * Refresh User's Google OAuth2 Access Token
     *
     * @param  [type] $account [description]
     * @return [type]         [description]
     */
    protected function getGoogleOfflineToken(SocialProvider $account, $code)
    {
        $config = \Config::get('services.google');

        // Config
        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];

        // Set Client
        $client = new Google_Client;
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        // User a localhost redirect to support proper host resolvtion. lvh.me points to 127.0.0.1
        // $route = app()->environment('dev') ? 'http://lvh.me/api/connected-accounts/google/callback' : route('social.callback', ['social' => 'google']);
        // $client->setRedirectUri($route);
        $client->setRedirectUri('postmessage');
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->setApprovalPrompt('force');

        return $client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * Undocumented function
     *
     * @param  AbstractProvider $provider
     * @param  SocialProvider   $account
     * @return void
     */
    protected function getOfflineToken(AbstractProvider $provider, SocialProvider $account, $code)
    {
        $token = [];
        switch ($account->slug) {
        case 'google':
            $token = $this->getGoogleOfflineToken($account, $code);
            break;
        }
        return $token;
    }

    /**
     * Undocumented function
     *
     * @param  AbstractProvider $provider
     * @param  SocialProvider   $account
     * @return void
     */
    protected function checkToken(AbstractProvider $provider, SocialProvider $account = null)
    {
        if ($account && $account->token && (!$account->token->expires_in || ($account->token->expires_in && Carbon::now()->greaterThan(Carbon::parse($account->token->expires_in))))
        ) {
            $token = null;
            switch ($account->slug) {
            case 'google':
                $token = $this->refreshGoogleToken($account);
                break;
            }
            if ($token) {
                $where = array_filter(Arr::only($account->token->getAttributes(), ['team_id', 'user_id', 'social_provider_id']));
                $newExpiry =  Carbon::now()->addSeconds(Arr::get($token, 'expires_in'));
                $accessToken = Arr::get($token, 'access_token') ?: Arr::get($token, 'token');
                if ($accessToken) {
                    $account->token->where($where)->update(
                        [
                        'token' => $accessToken,
                        'expires_in' => $newExpiry
                        ]
                    );
                    $account->token->token = Arr::get($token, 'access_token', $account->token->token);
                    $account->token->expires_in = $newExpiry;
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param  SocialProvider $account
     * @return void
     */
    protected function deleteToken(SocialProvider $social)
    {
        switch ($social->slug) {
        case 'google':
            return $this->deleteGoogleToken($social);
                break;
        }
    }

    /**
     * Undocumented function
     *
     * @param  SocialProvider $account
     * @param
     * @return bool
     */
    protected function deleteGoogleToken(SocialProvider $social)
    {
        $account = auth()->user()->socials()->whereSocialProviderId($social->id)->first();
        if ($account) {
            $config = \Config::get('services.google');

            // Config
            $client_id = $config['client_id'];
            $client_secret = $config['client_secret'];

            // Set Client
            $client = new Google_Client;
            $client->setClientId($client_id);
            $client->setClientSecret($client_secret);
            $token = $account->token->token;
            if (!$account->token->expires_in || ($account->token->expires_in && Carbon::now()->greaterThan(Carbon::parse($account->token->expires_in)))
            ) {
                $token = Arr::get($this->refreshGoogleToken($account), 'access_token');
            }
            return $token ? $client->revokeToken($token) : false;
        }
    }
}