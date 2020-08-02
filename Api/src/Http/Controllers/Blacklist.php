<?php namespace Nitm\Api\Controllers;

use October\Rain\Support\Facades\Flash;
use Backend;
use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Nitm\Api\Classes\Trivet;
use Nitm\Api\Models\Configs as RestfulConfig;


class Blacklist extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $requiredPermissions = ['nitm.api.blacklist'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $ip_blacklist = '';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Nitm.Restful', 'api', 'blacklist');
        SettingsManager::setContext('Nitm.Restful', 'blacklist');

        $this->ip_blacklist = RestfulConfig::get('ip_blacklist');
    }

    public function onSaveBlacklist()
    {
        Flash::success(trans('nitm.api::lang.blacklist.saved'));
        return RestfulConfig::set('ip_blacklist', Trivet::getInputs('ip_blacklist'));
    }

}
