<?php namespace Nitm\Api\Controllers;

use October\Rain\Support\Facades\Flash;
use Backend;
use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Nitm\Api\Models\Eventhandler;
use Nitm\Api\Models\Eventlog;
use Nitm\Api\Models\Configs as RestfulConfig;


class Eventhistory extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $requiredPermissions = ['nitm.api.eventlogs'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    /* Define admin_key for check in index.htm file */
    public $admin_key = '';

    /* Define days of log purge operation */
    public $purge_logs_after = 0;

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Nitm.Restful', 'api', 'eventhistory');
        SettingsManager::setContext('Nitm.Restful', 'eventhistory');

        $this->admin_key = RestfulConfig::get('admin_key');
        $this->purge_logs_after = RestfulConfig::get('purge_logs_after');
    }

    public function onEmptyLog()
    {
        Eventlog::truncate();
        Flash::success(trans('system::lang.request_log.empty_success'));
        return $this->listRefresh();
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = Eventlog::find($recordId))
                    continue;

                $record->delete();
            }

            Flash::success(trans('backend::lang.list.delete_selected_success'));
        } else {
            Flash::error(trans('backend::lang.list.delete_selected_empty'));
        }

        return $this->listRefresh();
    }

    public function getSendmethodOptions($which = null)
    {
        $evHandler = new Eventhandler;
        return $evHandler->getSendmethodOptions()[$which];
    }

}
