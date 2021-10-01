<?php
namespace Nitm\Api\Http\Controllers\Auth;

use Config;
use Exception;
use Carbon\Carbon;
use Google_Client;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\SocialProvider;
use App\Auth\SocialProviderManager;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Socialite\Two\AbstractProvider;
use MadWeb\SocialAuth\Events\SocialUserDetached;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Contracts\Factory as Socialite;
use MadWeb\SocialAuth\Exceptions\SocialUserAttachException;
use MadWeb\SocialAuth\Exceptions\SocialGetUserInfoException;
use MadWeb\SocialAuth\Controllers\SocialAuthController as BaseController;

/**
 * Class SocialAuthController.
 */
class TeamSocialAuthController extends BaseController
{
    use \Nitm\Api\Http\Controllers\Traits\CustomControllerTrait;

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

    public function refreshToken($team, SocialProvdier $social)
    {
        $provider = $this->socialite->driver($social->slug);
        $account = $team->socials()->whereSocialProviderId($social->id)->first();

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
    public function getAccounts($team)
    {
        $accounts = $team->socials()->get()->map(
            function ($account) {
                $provider = $this->socialite->driver($account->slug);
                $this->checkToken($provider, $account);
                return $account;
            }
        );
        return $this->printSuccess($accounts);
    }

    /**
     * If there is no response from the social network, redirect the user to the social auth page
     * else make create with information from social network.
     *
     * @param  SocialProvider $social bound by "Route model binding" feature
     * @return JsonResponse
     */
    public function getAccountCustom($team, SocialProvider $social)
    {
        $provider = $this->socialite->driver($social->slug);

        if (! empty($social->scopes)) {
            $social->override_scopes ? $provider->setScopes($social->scopes) : $provider->scopes($social->scopes);
        }

        $account = $this->getTeamAccount($team, $social);
        if($account) {
            $this->checkToken($provider, $account);
        }

        return $this->printSuccess($account);
    }

    protected function getTeamAccount($team, SocialProvider $social)
    {
        $account = $team->socials()->whereSocialProviderId($social->id)->first();
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
    public function callbackCustom(Request $request, $team, SocialProvider $social)
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
        if (! $socialUser) {
            throw new SocialGetUserInfoException(
                $social,
                trans('social-auth::messages.no_user_data', ['social' => $social->label])
            );
        }

        // if user is guest
        if (! $this->auth->check()) {
            return $this->processData($request, $social, $socialUser);
        }

        $redirect_path = $this->redirectPath();
        $user = $request->user();

        // if user already attached
        if ($user->isAttached($social->slug)) {
            throw new SocialUserAttachException(
                trans('social-auth::messages.user_already_attach', ['social' => $social->label]),
                $social
            );
        }

        //If someone already attached current socialProvider account
        if ($this->manager->socialUserQuery($socialUser->getId())->exists()) {
            throw new SocialUserAttachException(
                trans('social-auth::messages.someone_already_attach'),
                $social
            );
        }

        // $this->manager->attach($user, $socialUser);
        $this->manager->attachTeam($team, $socialUser, $request->input('offline_token'));

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
    public function callforward(Request $request, $team, SocialProvider $social)
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
            throw new SocialGetUserInfoException($social, $e->getMessage());
        }

        // if we have no social info for some reason
        if (! $socialUser) {
            throw new SocialGetUserInfoException(
                $social,
                trans('social-auth::messages.no_user_data', ['social' => $social->label])
            );
        }

        // if user is guest
        if (!auth()->check()) {
            return $this->processDataCustom($request, $team, $social, $socialUser);
        }

        $redirect_path = $this->redirectPath();
        $user = $request->user();

        // if user already attached
        if ($user->isAttached($type)) {
            return $this->printSuccess($this->getTeamAccount($team, $social));
        }

        //If someone already attached current socialProvider account
        if ($this->manager->socialUserQuery($socialUser->getId())->exists()) {
            throw new SocialUserAttachException(
                trans('social-auth::messages.someone_already_attach'),
                $social
            );
        }
        // if (!$user->isAttached($type)) {
        //     $this->manager->attach($user, $socialUser);
        // }
        $this->manager->attachTeam($team, $socialUser, Arr::get($token, 'refresh_token'));

        return $this->printSuccess($team->socials()->whereSlug($type)->first());
    }

    /**
     * Detaches social account for user.
     *
     * @param  Request        $request
     * @param  SocialProvider $social
     * @return array
     * @throws SocialUserAttachException
     */
    public function detachAccountCustom(Request $request, $team, SocialProvider $social)
    {
        /**
 * @var \MadWeb\SocialAuth\Contracts\SocialAuthenticatable $user
*/
        $user = $request->user();
        // $userSocials = $user->socials();
        $teamSocials = $team->socials();

        // if ($userSocials->count() === 1 and empty($user->{$user->getEmailField()})) {
        //     throw new SocialUserAttachException(
        //     trans('social-auth::messages.detach_error_last'),
        //     $social
        //     );
        // }

        // $result = $userSocials->detach($social->id);
        // if($result) {
            // Need to delete the remoe connection first
        $this->deleteToken($social, $team);
        $result = $teamSocials->detach($social->id);
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
    protected function processDataCustom(Request $request, $team, SocialProvider $social, SocialUser $socialUser)
    {
        //Checks by socialProvider identifier if user exists
        $existingUser = $this->manager->getUserByKey($socialUser->getId());

        //Checks if user exists with current socialProvider identifier, auth if does
        if ($existingUser) {
            return $this->printSuccess($this->getTeamAccount($team, $social));
        }

        //Checks if socialProvider email exists
        if ($social_user_email = $socialUser->getEmail()) {
            //Checks if account exists with socialProvider email, auth and attach current socialProvider if does
            $existingUser = $this->userModel->where('email', $social_user_email)->first();
            if ($existingUser) {
                // $this->login($existingUser);

                // $this->manager->attach($request->user(), $socialUser);
                $this->manager->attachTeam($team, $socialUser, $request->input('offline_token'));

                return $this->printSuccess($this->getTeamAccount($team, $social));
            }
        }

        //If account for current socialProvider data doesn't exist - create new one
        // $newUser = $this->manager->createNewUser($this->userModel, $social, $socialUser);

        return $this->printSuccess($this->getTeamAccount($team, $social));
    }

    /**
     * Refresh User's Google OAuth2 Access Token
     *
     * @param  [type] $account [description]
     * @return [type]         [description]
     */
    protected static function refreshGoogleToken(SocialProvider $account)
    {
        $config = \Config::get('services.google');

        // Config
        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];

        // User
        $token = $account->token->token;
        $refreshToken = $account->token->offline_token;
        $expiresIn = $account->token->expires_in;

        // If current date exceeds expired date request new access token
        if (Carbon::now()->greaterThan(Carbon::parse($account->token->expires_in)) && !empty($refreshToken)) {

            // Set Client
            $client = new Google_Client;
            $client->setClientId($client_id);
            $client->setClientSecret($client_secret);
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');
            $client->refreshToken($refreshToken);

            return $client->getAccessToken();
        }

        return false;
    }

    /**
     * Refresh User's Google OAuth2 Access Token
     *
     * @param  [type] $account [description]
     * @return [type]         [description]
     */
    protected static function getGoogleOfflineToken(SocialProvider $account, $code)
    {
        $config = \Config::get('services.google');

        // Config
        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];

        // Set Client
        $client = new Google_Client;
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri('postmessage');
        $client->setAccessType('offline');
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
        if ($account && (            !$account->token->expires_in
            || ($account->token->expires_in && Carbon::now()->greaterThan(Carbon::parse($account->token->expires_in))))
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
                $account->token->where($where)->update(
                    [
                    'token' => Arr::get($token, 'access_token', Arr::get($token, 'token')),
                    'expires_in' => $newExpiry
                    ]
                );
                $account->token->token = Arr::get($token, 'access_token', $account->token->token);
                $account->token->expires_in = $newExpiry;
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param  SocialProvider $account
     * @return void
     */
    protected function deleteToken(SocialProvider $social, $team)
    {
        switch ($social->slug) {
        case 'google':
            return $this->deleteGoogleToken($social, $team);
            break;
        }
    }

    /**
     * Undocumented function
     *
     * @param  SocialProvider $account
     * @param  Team           $team
     * @return bool
     */
    protected function deleteGoogleToken(SocialProvider $social, $team)
    {
        $account = $team->socials()->whereSocialProviderId($social->id)->first();
        if($account) {
            $config = \Config::get('services.google');

            // Config
            $client_id = $config['client_id'];
            $client_secret = $config['client_secret'];

            // Set Client
            $client = new Google_Client;
            $client->setClientId($client_id);
            $client->setClientSecret($client_secret);
            $token = $account->token->token;
            if(!$account->token->expires_in
                || ($account->token->expires_in && Carbon::now()->greaterThan(Carbon::parse($account->token->expires_in)))
            ) {
                $token = Arr::get($this->refreshGoogleToken($account), 'access_token');
            }
            return $token ? $client->revokeToken($token) : false;
        }
    }
}