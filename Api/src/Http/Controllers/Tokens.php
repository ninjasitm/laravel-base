<?php

namespace Nitm\Api\Controllers;

use October\Rain\Support\Facades\Flash;
use Backend;
use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Nitm\Api\Models\Token;
use Nitm\Api\Models\Configs as RestfulConfig;

class Tokens extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

   //  public $requiredPermissions = ['nitm.api.logs'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    /* Define admin_key for check in index.htm file */
    public $admin_key = '';

    /* Define days of log purge operation */
    public $purge_logs_after = 0;

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Nitm.Restful', 'api', 'tokens');
        SettingsManager::setContext('Nitm.Restful', 'tokens');

        $this->admin_key = RestfulConfig::get('admin_key');
        $this->purge_logs_after = RestfulConfig::get('purge_logs_after');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = Token::find($recordId)) {
                    continue;
                }

                $record->delete();
            }

            Flash::success(trans('backend::lang.list.delete_selected_success'));
        } else {
            Flash::error(trans('backend::lang.list.delete_selected_empty'));
        }

        return $this->listRefresh();
    }
}
