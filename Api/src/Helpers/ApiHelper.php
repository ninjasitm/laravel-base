<?php

namespace Nitm\Api\Helpers;

use Auth;
use Nitm\Api\Models\Configs as RestfulConfig;
use Nitm\Api\Classes\Rest;
use Nitm\Api\Classes\Ip;
use Nitm\Api\Classes\Trivet;

class ApiHelper
{
    /**
    * Holds REST Class instance.
    */
    public $rest;
    
    protected $component;
    
    /**
    * Holds the Request instance.
    *
    * @var [type]
    */
    
    /**
    * Starter method of the component.
    */
    public function __construct($component)
    {
        $this->rest = Rest::instance();
        $this->component = $component;
    }
    
    /**
    * Helper method for Api component's "onRun".
    *
    * @param $apiStatus
    */
    public function checkIfApiActivated($apiStatus)
    {
        if (!RestfulConfig::get('api_status') || $apiStatus === 'off') {
            Trivet::addApiLog(0, 503);
            $this->rest->response(503, trans('nitm.api::lang.responses.maintenance'));
        }
    }
    
    /**
    * Helper method for Api component's "onRun".
    */
    public function checkIfScheduleStarted()
    {
        if (RestfulConfig::get('schedule_status')) {
            $current_time = date('Y-m-d H:i:s');
            $start = RestfulConfig::get('api_schedule_start');
            $end = RestfulConfig::get('api_schedule_end');
            
            if ($current_time > $start && $current_time < $end) {
                Trivet::addApiLog(0, 503);
                $this->rest->response(503, trans('nitm.api::lang.responses.maintenance'));
            }
        }
    }
    
    /**
    * Helper method for Api component's "onRun".
    */
    public function checkIfIpBlocked()
    {
        $ip = Ip::instance();
        
        $blacklistDb = RestfulConfig::get('ip_blacklist');
        $blacklist = explode("\r\n", $blacklistDb);
        
        foreach ($blacklist as $blackIP) {
            if (Ip::ipv4_between($ip->realIp(), $blackIP)) {
                Trivet::addApiLog(0, 403);
                $this->rest->response(403, trans('nitm.api::lang.responses.ip_blocked'));
            }
        }
    }
    
    /**
    * Helper method for Api component's "onRun"
    * Added check with user-credentials.
    */
    public function checkIfAuthCorrect($requiresAuth = true)
    {
        
        // If auth should checked with user credentials
        $apiToken = Trivet::getApiToken();
        $apiLogin = Trivet::getInputs('login');
        
        /*
        * First lets try logging in by API Token
        * @var [type]
        */
        $user = Cache::remember(['token-user', md5($apiToken)], [], function () use ($apiToken, $requiresAuth) {
            $token = \Nitm\Api\Models\Token::findToken($apiToken);
            // Need to do some cookie id checking
            // && $token->signature === \Nitm\Api\Models\Token::getCookieId()
            if ($token) {
                return $token->user->toArray();
            } elseif ($requiresAuth) {
                return $this->rest->response(403, trans('nitm.api::lang.responses.auth_mismatch', [
                'params' => 'You need to login!',
                ]));
            }
        }, 10);
        
        /*
        * If that's not valid lets see if the user sent a username and password
        */
        if ($user && !array_get($user, 'id') && $apiLogin) {
            $user = Cache::remember(['login-user', md5($apilogin)], [], function () use ($apiLogin, $requiresAuth) {
                $authWithUserCredentials = RestfulConfig::get('auth_with_user');
                $apiPassword = Trivet::getInputs('password');
                if ($authWithUserCredentials) {
                    if ($apiLogin && $apiPassword) {
                        try {
                            Auth::authenticate([
                            'login' => $apiLogin,
                            'password' => $apiPassword,
                            ], true);
                            
                            return \Auth::getUser()->toArray();
                        } catch (\Exception $e) {
                            if ($requiresAuth) {
                                Trivet::addApiLog(0, 400);
                                
                                return $this->rest->response(400, $e->getMessage());
                            }
                        }
                    } else {
                        if (!\Auth::getUser()) {
                            return false;
                        } else {
                            return \Auth::getUser()->toArray();
                        }
                    }
                }
            }, 10);
        }
        
        /*
        * Ok if they're still not logged in then did they send an auth key?
        */
        if ($user && !array_get($user, 'id') && Trivet::getInputs('auth')) {
            $adminKey = RestfulConfig::get('admin_key');
            $authKeysDb = RestfulConfig::get('external_keys');
            $authKeys = explode("\r\n", $authKeysDb);
            if (!Trivet::getInputs('auth') ||
                (Trivet::getInputs('auth') != $adminKey && !in_array(Trivet::getInputs('auth'), $authKeys))
            ) {
                $this->component->controller->setIsAdmin(true);
                Trivet::addApiLog(0, 400);
                if ($requiresAuth) {
                    return $this->rest->response(400, trans('nitm.api::lang.responses.auth_mismatch', ['params' => '(auth)']));
                }
            }
        } elseif ($user) {
            \Auth::setUser(new \Nitm\Api\Models\User($user));
        }
        
        return true;
    }
    
    /**
    * Helper method for Api component's "onRun".
    *
    * @param $doOps
    *
    * @return mixed
    */
    public function checkIfCRUD($doOps)
    {
        $allInputs = Trivet::getInputs();
        
        /* Check if "do" parameter not matching */
        if (!isset($allInputs['do']) || !array_key_exists($allInputs['do'], $doOps)) {
            Trivet::addApiLog(0, 400);
            $this->rest->response(400, trans('nitm.api::lang.responses.do_mismatch'));
        }
        
        /* Then it's ok to return right method name for calling */
        return $doOps[$allInputs['do']];
    }
    
    /**
    * Helper method for Api component's "onRun".
    *
    * @param $doOps
    *
    * @return mixed
    */
    public function checkIfAuthCRUD($doOps)
    {
        $allInputs = Trivet::getInputs();
        
        /* Check if "do" parameter not matching */
        if (!isset($allInputs['action']) || !array_key_exists($allInputs['action'], $doOps)) {
            Trivet::addApiLog(0, 400);
            $this->rest->response(400, trans('nitm.api::lang.responses.action_mismatch'));
        }
        
        /* Then it's ok to return right method name for calling */
        return $doOps[$allInputs['action']];
    }
}