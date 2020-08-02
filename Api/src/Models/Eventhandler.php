<?php namespace Nitm\Api\Models;

use DB;
use Model;
use October\Rain\Database\Traits\Validation;
use Nitm\Api\Classes\Sysmodels;


class Eventhandler extends Model
{
    use Validation;

    public $selectorFieldName = 'exporttable';

    protected $table = 'nitm_api_eventhandler';

    protected $jsonable = ['exportfields'];

    public $rules = [
        'model' => 'required',
        'action' => 'required',
        'url' => 'required|url',
        'sendmethod' => 'required',
    ];

    public function beforeCreate()
    {
        //$this->user_id = BackendAuth::getUser()->id;
    }

    /**
     * [getModelOptions description]
     * @return array  [description]
     */
    public function getModelOptions()
    {
        return array_merge(
            ['' => trans('nitm.api::lang.event_automation.select_model')],
            Sysmodels::instance()->getModelsNamespaces()
        );
    }

    /**
     * [getActionOptions description]
     * @return array  [description]
     */
    public function getActionOptions()
    {
        $actions = [
            '' => trans('nitm.api::lang.event_automation.select_action'),
            'creating' => 'creating'.trans('nitm.api::lang.event_automation.before'),
            'created' => 'created'.trans('nitm.api::lang.event_automation.after'),
            'updating' => 'updating'.trans('nitm.api::lang.event_automation.before'),
            'updated' => 'updated'.trans('nitm.api::lang.event_automation.after'),
            'saving' => 'saving'.trans('nitm.api::lang.event_automation.before'),
            'saved' => 'saved'.trans('nitm.api::lang.event_automation.after'),
            'deleting' => 'deleting'.trans('nitm.api::lang.event_automation.before'),
            'deleted' => 'deleted'.trans('nitm.api::lang.event_automation.after'),
            'restoring' => 'restoring'.trans('nitm.api::lang.event_automation.before'),
            'restored' => 'restored'.trans('nitm.api::lang.event_automation.after')
        ];

        return $actions;
    }

    /**
     * [getMethodOptions description]
     * @return array [description]
     */
    public function getSendmethodOptions()
    {
        $methods = [
            '' => trans('nitm.api::lang.event_automation.select_method'),
            'curl_post' => 'cURL (POST) (Recommended)',
            'stream_post' => 'Stream context (POST)',
            'socket_post' => 'fsockopen (POST)',
        ];

        return $methods;
    }

    /**
     * [getExporttableOptions description]
     * @return array  [description]
     */
    public function getExporttableOptions()
    {
        $dbTables = \Nitm\Utils\Classes\DbHelper::getTables();
        $dropdownTables = [];

        foreach ($dbTables as $key => $value)
            $dropdownTables[reset($value)] = reset($value);

        return $dropdownTables;
    }

}
