<?php

namespace Nitm\Api\Controllers;

use Nitm\Api\Helpers\Cache;
use Cms\Classes\Controller;
use Cms\Classes\CmsController;
use Cms\Classes\CodeParser;
use Cms\Classes\Page;
use Nitm\Api\Classes\Rest;
use Nitm\Api\Classes\Trivet;
use Nitm\Api\Models\Mapping;
use Nitm\Api\Models\Configs as RestfulConfig;
use October\Rain\Exception\ApplicationException;

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
class BaseApiController extends CmsController
{
    public $operations = [];
   /**
    * Holds the Timer's starting value.
    */
   public $startTime = 0;

   /**
    * Holds Data for final response.
    */
   public $data;

   /**
    * Paths for Helper Classes.
    */
   public $helperClasses = [
      'create' => 'Nitm\Api\Classes\ApiCreater',
      'read_all' => 'Nitm\Api\Classes\ApiReaderAll',
      'read' => 'Nitm\Api\Classes\ApiReader',
      'update' => 'Nitm\Api\Classes\ApiUpdater',
      'delete' => 'Nitm\Api\Classes\ApiDeleter',
   ];

   /**
    * Holds all relations.
    */
   public $all_relations = [];

   /**
    * Holds index key provided by user input.
    */
   public $where_index_key;

   /**
    * @var string The call result
    */
   protected $_result;

    /**
     * THe error result.
     *
     * @var string
     */
    protected $_errors;

    public $page;
    protected $controller;

    protected $isAdmin;

    public function __construct()
    {
        /* Get an instance of Rest Class */
      $this->rest = Rest::instance();

       /*
        * Define All Relations and Request Parameters
        */
        $requestParameter = Trivet::getInputs('req');
        $relation = Cache::remember(['api-mapping-'.$requestParameter], [], function () use (&$requestParameter) {
            $relation = Mapping::query()->where('reqparameter', '=', $requestParameter)->first();
           //By default requestParameters are in singular. Some APIs may request the plural
           if (!$relation) {
               $requestParameter = str_singular($requestParameter ?? "");
               $relation = Mapping::query()->where('reqparameter', '=', $requestParameter)->first();
               \Route::current()->setParameter('req', $requestParameter);
           }

            return $relation;
        }, 60, null, false, true);

        $dataType = null;
        if (Trivet::getInputs('req') == 'config') {
            \Route::current()->setParameter('key', 'req');
            $dataType = 'config-'.Trivet::getInputs('id');
        }
        if ($relation) {
            $dataType = $dataType ?? preg_replace('/[^\da-z]/i', '-', $relation->reqparameter);
            $this->request_parameters[] = Trivet::getInputs('req');
            $this->all_relations[Trivet::getInputs('req')] = $relation;
            $this->rest->dataType = $dataType;
        } else {
            $this->request_parameters[] = $this->rest->request;
            $route = explode('/', \Route::current()->getName());
            $route = array_pop($route);
            $this->rest->dataType = $route;
            $this->all_relations[$route] = [];
        }
        /* Start calculating the script runtime */
        $this->startTime = microtime(true);
    }

    public function setIsAdmin(bool $isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    public function requiresAuth()
    {
        return in_array($this->getOperation(), [
           'create', 'update', 'delete',
        ]);
    }

    protected function hasResult()
    {
        return isset($this->_result);
    }

    protected function setResult($result)
    {
        $this->_result = $result;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getErrorResponse()
    {
        return $this->rest->response($this->_errors['code'], $this->_errors, 'errors');
    }

    public function hasErrors()
    {
        return isset($this->_errors) && !empty($this->_errors);
    }


    /**
     * Get the actual resultof for the request.
     * This will call the applicable method to generate the result.
     * It will also handle all error formating for responses.
     *
     * @param string $methodToRun The operation to run
     *
     * @return array for Rest response
     */
    public function getResult($callable, $type = null)
    {
        $type = $type ?? $this->rest->dataType;
        $this->rest->dataType = $type ?? $this->rest->dataType;
        if (!$this->hasResult()) {
            $data = $this->tryResult($callable);
            if (!$this->_errors) {
                $this->_result = $this->rest->response(200, $data, $type, $this->getOperation() == 'read_all');
            } else {
                $this->_result = $this->getErrorResponse();
            }
        }

        return $this->_result;
    }/**
     * Get the actual result of for the request.
     * This will call the applicable method to generate the result.
     * It will also handle all error formating for responses.
     *
     * @param string $methodToRun The operation to run
     *
     * @return array for Rest response
     */
    public function tryResult($callable, $tryOnly = false)
    {
        /* Add API Log, return final response */
        try {
            $this->_errors = null;
            $this->data = call_user_func($callable);
            if (!$tryOnly) {
                $computePassedTime = microtime(true) - $this->startTime;
                Trivet::addApiLog($computePassedTime, 200);
                Trivet::addViewLog($this->getOperation(), [
                    'content_type' => Trivet::getInputs('req'),
                    'content_id' => Trivet::getInputs('id') ?? Trivet::getInputs('req'),
                 ], $computePassedTime);
                return $this->data;
            }
        } catch (\Exception $e) {
            $computePassedTime = microtime(true) - $this->startTime;
            Trivet::addApiLog($computePassedTime, $e->getCode() ?? 400, $e);
            // if (\App::environment() == 'dev' || Trivet::getInputs('showError') == true) {
            //     throw $e;
            // }
            switch (get_class($e)) {
                 case \October\Rain\Exception\ApplicationException::class:
                 case \October\Rain\Exception\ValidationException::class:
                 case \October\Rain\Database\ModelException::class:
                 $code = $e->getCode() ?: 400;
                 break;

                 case \October\Rain\Auth\AuthException::class:
                 $code = 400;
                 break;

                 default:
                 $code = $e->getCode() ?: 500;
                 break;
              }
            if (\Config::getEnvironment() == 'dev' && !\Request::ajax()) {
                $this->_errors = [
                   'code' => $code,
                   'message' => $e->getMessage(),
                   'line' => $e->getLine(),
                   'file' => $e->getFile(),
                   'trace' => $e->getTrace()
                ];
            } else {
                $this->_errors = [
                   'code' => $code,
                   'message' => $e->getMessage(),
                ];
            }

            return false;
        }
    }

    /**
     * We need this function when using REST practices. Specifically if we do not specify the route action we need to imply it from the controller action.
     * i.e.: Nitm\Api\Components\Api@readData results in:
     * action = readData
     * operation = read.
     *
     * @return string The intended operation
     */
    public function getOperation()
    {
        $actionParts = explode('@', $this->getRouter()->current()->getActionName());
        $specifiedAction = array_pop($actionParts);
        $action = null;

        if (in_array($specifiedAction, $this->operations)) {
            $action = array_flip($this->operations)[$specifiedAction];
        } elseif (isset($this->operations[$specifiedAction])) {
            $action = $specifiedAction;
        }

        if (!$action) {
            throw new ApplicationException(trans('nitm.api::lang.responses.do_mismatch', [
             'action' => $specifiedAction,
           ]));
        }

        if (!Trivet::getInputs('do')) {
            $this->getRouter()->current()->setParameter('do', $action);
        }

        return $action;
    }

    /**
     * Check if "req" parameter matches with any relation or table, else stop.
     */
    public function checkIfRequestOk()
    {
        if (RestfulConfig::get('direct_table_output')) {
            /* Check if "req" parameter matches with any relation or table */
            if (
                !in_array(Trivet::getInputs('req'), $this->request_parameters) &&
                !Schema::hasTable(Trivet::getInputs('req'))
            ) {
                Trivet::addApiLog(0, 400);
                throw new ApplicationException(trans('nitm.api::lang.responses.req_mismatch', [
                   'resource' => Trivet::getInputs('req'),
                ]));
            }
        } else {
            /* Check if "req" parameter matches with any relation */
            if (!in_array(Trivet::getInputs('req'), $this->request_parameters)) {
                Trivet::addApiLog(0, 400);
                throw new ApplicationException(trans('nitm.api::lang.responses.req_mismatch', [
                   'resource' => Trivet::getInputs('req'),
                ]));
            }
        }
    }

    /**
     * Check if relation set as 'read_only' and API user wants to do C,U,D.
     */
    public function checkIfReadOnly()
    {
        /*
         * So be extra careful when allowed to direct table access,
         * you will allow "create", "update" and "delete" Ops reluctantly
         */
        $mapping = array_get($this->all_relations, Trivet::getInputs('req'));
        if (is_object($mapping) && $mapping->read_only) {
            if (Trivet::getInputs('do') == 'create' ||
                Trivet::getInputs('do') == 'update' ||
                Trivet::getInputs('do') == 'delete'
            ) {
                Trivet::addApiLog(0, 400);
                throw new ApplicationException(
                    trans('nitm.api::lang.responses.read_only', ['reqtype' => Trivet::getInputs('req')])
                );
            }
        }
    }

    public function getOperationFields()
    {
        return array_get($this->fields, $this->getOperation(), []);
    }

    /**
     * Returns a routing parameter.
     *
     * @param string $name    Routing parameter name
     * @param string $default Default to use if none is found
     *
     * @return string
     */
    public function param($name, $default = null)
    {
        return \Route::current()->getParameter($name, $default);
    }

    /**
     * Check if mandatory fields and 'key' field is present.
     *
     * @param $fields
     */
    public function checkIfParamsOk()
    {
        /* Check if 'we have mapping for this request */
         if (!isset($this->request_parameters)) {
             Trivet::addApiLog(0, 420);
             throw new ApplicationException(trans('nitm.api::lang.responses.req_mismatch', [
                'resource' => implode('/', array_filter([Trivet::getInputs('req'), Trivet::getInputs('id')])),
            ]));
         }
        $fields = $this->getOperationFields();       /* Check "Allow Requesting with Index Keys" option for relation */
        if (in_array(Trivet::getInputs('req'), $this->request_parameters)) {
            $mapping = array_get($this->all_relations, Trivet::getInputs('req'));
            if ($mapping) {
                /* If index keys are not allowed, "key" is mandatory */
                $fields['mandatory'] = array_merge($fields['mandatory'], $fields['optional']);
            }
        }

        /* Check if 'key' field mandatory and isset */
        if (in_array('key', $fields['mandatory'])) {
            if (!Trivet::getInputs('key') || !Trivet::getInputs(Trivet::getInputs('key'))) {
                Trivet::addApiLog(0, 420);
                throw new ApplicationException(trans('nitm.api::lang.responses.key_field_mismatch', [
                    'key' => Trivet::getInputs('key'),
                ]));
            }
        }

        /* Check for mandatory fields (other than key) */
        foreach ($fields['mandatory'] as $value) {
            /* If Authenticate only with User Credentials option activated, pass auth param */
            if (RestfulConfig::get('auth_with_user') && $value == 'auth') {
                continue;
            }

            if (!Trivet::getInputs($value)) {
                Trivet::addApiLog(0, 420);
                throw new ApplicationException(trans('nitm.api::lang.responses.mandatory_fields_mismatch', [
                    'field' => $value,
                ]));
            }
        }
    }


    //
    // Page helpers
    //

    /**
     * Looks up the URL for a supplied page and returns it relative to the website root.
     *
     * @param mixed $name Specifies the Cms Page file name.
     * @param array $parameters Route parameters to consider in the URL.
     * @param bool $routePersistence By default the existing routing parameters will be included
     * @return string
     */
    public function pageUrl($name, $parameters = [], $routePersistence = true)
    {
        if (!$name) {
            return $this->currentPageUrl($parameters, $routePersistence);
        }

        /*
         * Second parameter can act as third
         */
        if (is_bool($parameters)) {
            $routePersistence = $parameters;
        }

        if (!is_array($parameters)) {
            $parameters = [];
        }

        if ($routePersistence) {
            $parameters = array_merge($this->router->getParameters(), $parameters);
        }

        if (!$url = $this->router->findByFile($name, $parameters)) {
            return null;
        }

        if (substr($url, 0, 1) == '/') {
            $url = substr($url, 1);
        }

        $routeAction = 'Cms\Classes\CmsController@run';
        $actionExists = Route::getRoutes()->getByAction($routeAction) !== null;

        if ($actionExists) {
            return URL::action($routeAction, ['slug' => $url]);
        } else {
            return URL::to($url);
        }
    }

    /**
     * Looks up the current page URL with supplied parameters and route persistence.
     * @param array $parameters
     * @param bool $routePersistence
     * @return null|string
     */
    public function currentPageUrl($parameters = [], $routePersistence = true)
    {
        if (!$currentFile = $this->page->getFileName()) {
            return null;
        }

        return $this->pageUrl($currentFile, $parameters, $routePersistence);
    }

    /**
     * Create a blank page for the component.
     *
     * @param array $routerParameters THe parameters for the route
     * @param array $pageSettings     The settings for the generated page
     *
     * @return CodeBase The code base page object
     */
    protected function spoofPageCode()
    {
        if (!isset($this->page)) {
            $this->page = new Page();
        }
        $parser = new CodeParser($this->page);
        $pageObj = $parser->source($this->page, 'no-layout', $this);

        return $pageObj;
    }
}
