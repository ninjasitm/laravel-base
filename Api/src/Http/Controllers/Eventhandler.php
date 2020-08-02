<?php namespace Nitm\Api\Controllers;

use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;
use Nitm\Api\Classes\Trivet;


class Eventhandler extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $requiredPermissions = ['nitm.api.eventhandler'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Nitm.Restful', 'api', 'eventhandler');
    }

    public function onTableChange()
    {
        $this->vars['dbFields'] = Trivet::getDbFields();
        return $this->vars['dbFields'];
    }

}
