<?php

namespace Nitm\Api\Controllers;

use Lang;
use Auth as Authenticator;
use Mail;
use Event;
use Input;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use RainLab\User\Models\Settings as UserSettings;
use Nitm\Content\Models\User;
use Nitm\Api\Classes\Trivet;
use RainLab\User\Models\User as UserModel;

/**
 * This class will allow for creating a dynamically extendable controller that will add specific methods to the object.
 *
 * For example when using the Illiminate/Routing/Route route an instance of this controller will be extended to attach the relevant method;
 *
 * Api::extend(function ($model) {
 *    $model->addDynamicMethod('apiFunction', function (use($model) {
 *       return $model->apiFunction();
 *    }))
 * })
 */
class AuthController extends BaseApiController
{
    protected $recovery;

    /**
    * Define methods for operations
    * 'do parameter' => 'method name'.
    */
   public $operations = [
      'login' => 'loginAccount',
      'ping' => 'pingAccount',
      'profile' => 'profileAccount',
      'logout' => 'logoutAccount',
      'reset' => 'resetAccount',
      'recover' => 'recoverAccount',
      'register' => 'registerAccount',
      'activate' => 'activateAccount',
      'deactivate' => 'deactivateAccount',
      'delete' => 'deleteAccount',
      'update' => 'updateAccount',
      'restore' => 'restoreAccount',
   ];

   /**
    * Parameters expected from GET/POST for each CRUDs
    * mandatory    => Exact needed parameters
    * purge        => Deletable before doing Ops
    * optional     => Optional for defining parameters.
    */
   public $fields = [
      'ping' => [
           'mandatory' => [],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post'],
      ],
      'profile' => [
           'mandatory' => [],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post', 'get'],
      ],
      'login' => [
           'mandatory' => ['login', 'password'],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post'],
      ],
      'logout' => [
           'mandatory' => ['auth'],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post'],
      ],
      'reset' => [
           'mandatory' => ['email'],
           'purge' => ['type'],
           'optional' => ['key'],
           'methods' => ['post'],
      ],
      'recover' => [
           'mandatory' => ['code'],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['get', 'post'],
      ],
      'register' => [
           'mandatory' => ['username', 'password', 'password_confirmation', 'email', 'name'],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post'],
      ],
      'update' => [
           'mandatory' => [],
           'purge' => ['type'],
           'optional' => ['login', 'password', 'password_confirmation', 'email', 'name', 'job', 'about', 'company', 'webpage', 'gender'],
           'methods' => ['post', 'put', 'patch'],
      ],
      'activate' => [
           'mandatory' => ['code'],
           'purge' => ['type'],
           'optional' => [],
           'methods' => ['post'],
      ],
   ];

   /**

    * Method for returning one row from table
    * POST /auth/login.
    *
    * @param string $login    Username|email
    * @param string $password Password for user
    *
    * @return Response
    */
   public function loginAccount()
   {
       return $this->getResult(function () {
           // From Rainlab/user/Components/Account
           $data = Input::all();
           $rules = [];

           $rules['login'] = $this->loginAttribute() == UserSettings::LOGIN_USERNAME
            ? 'required|between:2,255'
            : 'required|email|between:6,255';

           $rules['password'] = 'required|between:4,255';

           if (!array_key_exists('login', $data)) {
               $data['login'] = Input::get('login', Input::get('username', Input::get('email')));
           }

           $validation = Validator::make($data, $rules);
           if ($validation->fails()) {
               //  return $this->rest->response(400, $validation);
               throw new ValidationException($validation);
           }

           /*
            * Authenticate user
            */
           $credentials = [
               'login' => array_get($data, 'login') ?: Trivet::getInputs('login'),
               'password' => array_get($data, 'password') ?: Trivet::getInputs('password'),
           ];

           Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);
           $user = Authenticator::authenticate($credentials, true);
           if (is_object($user)) {
               Authenticator::login($user, true);
               $user->checkToken();
           }

           return $user;
       }, 'user');
   }

     /**
      * Logout a currently logged in user
      * POST /auth/logout.
      *
      * @return Response
      */
     public function logoutAccount()
     {
         return $this->getResult(function () {
             Authenticator::logout();

             return  true;
         }, 'user');
     }

    /**
     * Logout a currently logged in user
     * POST /auth/logout.
     *
     * @return Response
     */
    public function pingAccount()
    {
        return $this->getResult(function () {
            $tokenValue = Trivet::getApiToken();

            $user = null;
            if ($tokenValue) {
                $token = \Nitm\Api\Models\Token::findToken($tokenValue);
                if ($token) {
                    $user = $token->user;
                }
            }

            return $user;
        }, 'user');
    }

    /**
     * Get a user's full account information
     * POST|GET /auth/profile.
     *
     * @return Response
     */
    public function profileAccount()
    {
        return $this->getResult(function () {
            $tokenValue = Trivet::getApiToken();

            $user = null;
            if ($tokenValue) {
                $token = \Nitm\Api\Models\Token::findToken($tokenValue);
                if ($token) {
                    $user = \Nitm\Content\Models\User::apiFind($token->user->id);
                }
            }

            return $user;
        }, 'user');
    }

   /*
   *

    * Update an existing user
    * POST /auth/update/{id}.
    *
    * @param string $id       The user ID
    * @param string $email    Email
    * @param string $gender   Gender
    * @param string $job      Job
    * @param string $about    About
    * @param string $company  Company
    * @param string $webpage  Webpage/Website
    * @param string $password Password
    *
    * @return Response
    */
   public function updateAccount()
   {
       return $this->getResult(function () {
           if (!Authenticator::getUser()) {
               return $this->rest->response(401, trans('rainlab.user::lang.account.unauthorized'));
           }

           $user = Authenticator::createUserModel()->apiFind(Authenticator::getUser()->id);
           if (!$user->can('update')) {
               throw new ApplicationException(trans('nitm.content::lang.errors.no_permission'), 403);
           }

           $data = Trivet::filterInput(Input::all());

           $user->fill($data);

         //   $user->avatar = $data['avatar'];
           $user->save();

         /*
           * Password has changed, reauthenticate the user
           */
         if (strlen(Input::get('password'))) {
             Authenticator::login($user->reload(), true);
         }

         //Customize the data type for the rest response
         return $user;
       }, 'user');
   }

   /**
    * Activate the user
    * GET /auth/activate/{code}.
    *
    * @param string $code Activation code
    *
    * @return Response
    */
   public function activateAccount()
   {
       return $this->getResult(function () {
           $code = Input::get('code', null);

           if (!$code) {
               throw new ApplicationException(trans('rainlab.user::lang.account.invalid_activation_code'), 400);
           }

           /*
            * Break up the code parts
            */
           $parts = explode('!', $code);
           if (count($parts) != 2) {
               throw new ValidationException([
                  'code' => trans('rainlab.user::lang.account.invalid_activation_code')
               ]);
           }

           list($userId, $code) = $parts;

           if (!strlen(trim($userId)) || !($user = Authenticator::findUserById($userId))) {
               throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'), 403);
           }

           if (!$user->attemptActivation(trim($code))) {
               throw new ValidationException([
                  'code' => trans('rainlab.user::lang.account.invalid_activation_code')
               ]);
           }

         /*
          * Sign in the user
          */
         Authenticator::login($user);

           $user->checkToken();
           return $user;
       }, 'user');
   }

   /**
    * Recover the user's password/account
    * GET /auth/recover/{code?}.
    *
    * @param string $code Recovery code
    *
    * @return Response
    */
   public function recoverAccount()
   {
       return $this->getResult(function () {
           $code = Input::get('code', null);
           $rules = [
               'code'     => 'required',
               'password' => 'required|between:4,255'
           ];

           $validation = Validator::make(Input::all(), $rules);
           if ($validation->fails()) {
               throw new ValidationException($validation);
           }

           /*
            * Break up the code parts
            */
           $parts = explode('!', $code);
           if (count($parts) != 2) {
               throw new ValidationException([
                  'code' => trans('rainlab.user::lang.account.invalid_activation_code')
               ]);
           }

           list($userId, $code) = $parts;

           if (!strlen(trim($userId)) || !($user = Authenticator::findUserById($userId))) {
               throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
           }

           if (!$user->attemptResetPassword($code, Input::get('password'))) {
               throw new ValidationException([
                  'code' => trans('rainlab.user::lang.account.invalid_activation_code')
               ]);
           }
           
           $user->checkToken();
           return $user;
       }, 'user');
   }/**
    * Reset the user's password/account
    * GET /auth/reset/{code?}.
    *
    * @param string $code Reset code
    *
    * @return Response
    */
   public function resetAccount()
   {
       return $this->getResult(function () {
           $rules = [
               'email' => 'required|email|between:6,255'
           ];

           $email = Input::get('email');
           $validation = Validator::make([
             'email' => $email
          ], $rules);
           if ($validation->fails()) {
               throw new ValidationException($validation);
           }

           if (!$user = UserModel::findByEmail($email)) {
               throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
           }

           $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
           $link = rtrim(\Config::get('app.webUrl'), '/').'/user/recover?'.http_build_query([
              $this->getRecovery()->property('paramCode') => $code,
           ]);

           $data = [
               'name' => $user->name,
               'link' => $link,
               'code' => $code
           ];

           \Mail::send('rainlab.user::mail.restore', $data, function ($message) use ($user) {
               $message->to($user->email, $user->full_name);
           });

           return true;
       }, 'user');
   }

   /**

    * Delete an existing user.
    *
    * @param string $id       The user ID
    * @param string $password Password
    */
   public function deactivateAccount()
   {
       return $this->getResult(function () {
           if (!$user = $this->getAuth()->user()) {
               return;
           }

           if (!$user->checkHashValue('password', Input::get('password'))) {
               throw new ValidationException([
                  'password' => trans('rainlab.user::lang.account.invalid_deactivation_pass')
               ]);
           }

           $user->delete();
           self::logout();

           return $user;
       }, 'user');
   }

   /**

    * Register/create a new user
    * POST /auth/create.
    *
    * @param string $name     Name
    * @param string $username Username
    * @param string $email    Email
    * @param string $password Password
    * @param string $password_confirmation Password Confirmation
    *
    * @return Response
    */
   public function registerAccount()
   {
       return $this->getResult(function () {
           if (!UserSettings::get('allow_registration', true)) {
               throw new ApplicationException(trans('rainlab.user::lang.account.registration_disabled'));
           }

        /*
         * Validate input
         */
        $data = Input::all();

           if (!array_key_exists('password_confirmation', $data)) {
               $data['password_confirmation'] = Input::get('password');
           }

           $rules = [
            'username' => 'required|unique:users|between:6,255',
            'email' => 'required|unique:users',
            'password' => 'required|between:4,255',
        ];

           if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
               $rules['username'] = 'required|between:2,255';
           }

           $validation = Validator::make($data, $rules);
           if ($validation->fails()) {
               throw new ValidationException($validation);
           }

        /*
         * Register user
         */
        $requireActivation = UserSettings::get('require_activation', true);
           $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
           $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
           $user = Authenticator::register($data, $automaticActivation);

        /*
         * Activation is by the user, send the email
         */
        if ($userActivation) {
            $user->checkToken();
            $this->sendActivationEmail($user);
        }

        /*
         * Automatically activated or not required, log the user in
         */
        if ($automaticActivation || !$requireActivation) {
            Authenticator::login($user);
        }

           return $user;
       }, 'user');
   }

   /**

    * Activate an account.
    *
    * @return [type] [description]
    */
   public function sendActivationEmail($user = null)
   {
       $user = $user ?: Authenticator::getUser();
       if (!$user == Authenticator::getUser()) {
           throw new ApplicationException(trans('rainlab.user::lang.account.login_first'));
       }

       if ($user->is_activated) {
           throw new ApplicationException(trans('rainlab.user::lang.account.already_active'));
       }
       $code = implode('!', [$user->id, $user->getActivationCode()]);
       $link = rtrim(\Config::get('app.webUrl'), '/').'/user/activate?'.http_build_query([
           $this->getAuth()->property('paramCode') => $code,
        ]);

       $data = [
           'name' => $user->name,
           'link' => $link,
           'code' => $code,
      ];

       Mail::send('rainlab.user::mail.activate', $data, function ($message) use ($user) {
           $message->to($user->email, $user->name);
       });

       return $data;
   }

   /**

    * Get the account component for authorization.
    *
    * @return \Cms\Classes\ComponentManager Auth component
    */
   protected function getAuth()
   {
       if (!isset($this->auth)) {
           $this->auth = \Cms\Classes\ComponentManager::instance()->makeComponent('account', $this->spoofPageCode());
       }

       return $this->auth;
   }

   /**
    * Get the account component for account recovery.
    *
    * @return \Cms\Classes\ComponentManager ResetPassword component
    */
   protected function getRecovery()
   {
       if (!isset($this->recovery)) {
           $this->recovery = \Cms\Classes\ComponentManager::instance()->makeComponent('resetPassword', $this->spoofPageCode());
       }

       return $this->recovery;
   }

   /**
    * Returns the login model attribute.
    */
   protected function loginAttribute()
   {
       return UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
   }
}
