<?php

namespace Nitm\Content\Behaviors;

use Str;
use Backend;
use Backend\CLasses\BackendController;
use Nitm\Content\Models\PageConfig;

/**
* Form Controller Behavior
* Adds features for working with backend forms.
*
* @author Alexey Bobkov, Samuel Georges
*/
class FormController extends \Backend\Behaviors\FormController
{
    /**
    * Behavior constructor.
    *
    * @param Backend\Classes\Controller $controller
    */
    public function __construct($controller)
    {
        parent::__construct($controller);
        /*
        * Build configuration
        */
        if (BackendController::$action == 'update') {
            $model = PageConfig::find(BackendController::$params[0]);
            $this->controller->formConfig = '$/'.$model->getNamespacedPath().'/controllers/'.$model->page.'/'.$this->controller->formConfig;
            $this->config = $this->makeConfig($this->controller->formConfig, $this->requiredConfig);
        } else {
            $this->config = $this->makeConfig($this->controller->formConfig, $this->requiredConfig);
        }
        $this->config->modelClass = Str::normalizeClassName($this->config->modelClass);
    }
}