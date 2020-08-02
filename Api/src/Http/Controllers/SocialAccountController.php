<?php

namespace Nitm\Api\Controllers;

use Lang;
use Input;
use Session;
use Cms\Classes\Page;
use Cms\Classes\CodeParser;
use Cms\Classes\Controller;
use ApplicationException;
use Nitm\Api\Classes\Trivet;
use Nitm\Content\Models\User;

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
class SocialAccountController extends AuthController
{
    /**
    * Parameters expected from GET/POST for each CRUDs
    * mandatory    => Exact needed parameters
    * purge        => Deletable before doing Ops
    * optional     => Optional for defining parameters.
    */
   public $fields = [
       'connect' => [
           'mandatory' => [],
           'purge' => ['type'],
           'optional' => ['network', 'state', 'code'],
           'methods' => ['post', 'get'],
       ],
   ];

   /**
    * Operations supported by this component.
    *
    * @var [type]
    */
   public $operations = [
     'connect' => 'connectAccount',
  ];

   /**
    * Define methods for operations
    * 'do parameter' => 'method name'.
    */
   public $networks = [
      'facebook' => 'connectFacebook',
      'google' => 'connectGoogle',
      'twitter' => 'connectTwitter',
   ];

    public function addCss($name, $attributes = [])
    {
    }

   /**
    * Activate the user
    * GET /connect/:network.
    *
    * @param string $network The social network name [google|facebook|twitter]
    * @param string $code    The social network oAuth code
    * @param string $state   The connection oAuth state
    *
    * @return Response
    */
   public function connectAccount($network = null)
   {
       //If this is an attempt to connect an account then check to see if the account was just connected
       if (Session::has('connect_key')) {
           Session::forget('connect_key');

           return User::find(Session::get('user_id'));
       }
       $network = $network ?: Trivet::getInputs('network');
       $manager = \Cms\Classes\ComponentManager::instance();
       if ($manager->hasComponent($network)) {
           $connector = $manager->makeComponent($network, $this->spoofAccountPageObj($manager->resolve($network)));
           if (Input::has('state') && Input::has('code')) {
               $connector->setProperty('redirect', $this->urlOnConnect());
               $connector->controller = $this;
               try {
                   $result = call_user_func([$connector, 'onRun']);
               } catch (\Exception $e) {
                   throw $e;

                   return $this->rest->response(400, $e->getMessage());
               }
               $userId = Session::get('user_id');
               $user = null;
               if ($userId) {
                   $user = \Nitm\Content\Models\User::find($userId);
               }

               if (is_object($user)) {
                   $user->checkToken();
                   $connectToken = md5($user->apiToken->token);
                   Session::flash('user_id', $userId);
                   Session::flash('connect_key', $connectToken);

                   return redirect($this->urlOnConnect().'?provider='.$network);
               } else {
                   return $result;
               }
           } else {
               return call_user_func([$connector, 'on'.ucfirst($network)]);
           }
       } else {
           throw new ApplicationException(trans('nitm.api::lang.responses.auth_no_provider', [
               'provider' => $network,
            ]));
       }
   }

   /**
    * Create a blank page for the component.
    *
    * @param array $routerParameters THe parameters for the route
    * @param array $pageSettings     The settings for the generated page
    *
    * @return CodeBase The code base page object
    */
   protected function spoofAccountPageObj($controllerClass)
   {
       if (!isset($this->page)) {
           $this->page = new Page();
       }
       if (!isset($this->controller)) {
           $this->controller = $this;
       }
       $parser = new CodeParser($this->page);
       $pageObj = $parser->source($this->page, 'no-layout', $this->controller);

       return $pageObj;
   }

    /**
     * Hacking out need for Controller $controller. We want to redirect to the Web UI to have it send the authorization for the API.
     *
     * @return [type] [description]
     */
    public function currentPageUrl($parameters = [], $routePersistence = true)
    {
        $network = Trivet::getInputs('network');

        return \Config::get('app.apiUrl').'/'.\Config::get('app.apiVersion').'/connect/'.$network;
    }

    protected function urlOnConnect()
    {
        return \Config::get('app.webUrl').'/profile';
    }
}
