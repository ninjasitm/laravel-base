<?php

namespace Nitm\Api\Models;

use Model;
use Nitm\Api\Classes\Trivet;

class Mapping extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $selectorFieldName = 'relatedtable';

    protected $table = 'nitm_api_mappings';

    protected $jsonable = ['responsefields'];

    public $rules = [
        'reqparameter' => 'required|unique:nitm_api_mappings',
        'relatedtable' => 'required',
        'responsefields' => 'required',
    ];

    public $customMessages = [
        'reqparameter.required' => 'nitm.api::lang.mapping.reqparameter_required',
        'reqparameter.unique' => 'nitm.api::lang.mapping.reqparameter_unique',
        'relatedtable.required' => 'nitm.api::lang.mapping.relatedtable_required',
        'responsefields.required' => 'nitm.api::lang.mapping.responsefields_required',
    ];

    public $hasMany = [
        //'eventhandler' => ['Nitm\Api\Models\Eventhandler']
    ];

    public function getRelatedtableOptions()
    {
        $dropdownTables = [];

        $classes = Trivet::getPluginModelClasses('Nitm\\Content');
        foreach ($classes as $modelName => $class) {
            $dropdownTables[$class] = str_replace('\\', '.', $class);
        }

        $dbTables = \Nitm\Content\Classes\DbHelper::getTableNames();
        $tables = [];
        foreach ($dbTables as $key => $value) {
            $tables[$value] = $value;
        }
        $tables = array_filter($tables, function ($key) {
            return strpos($key, 'bucardo') === false;
        });
        ksort($tables);

        $dropdownTables = array_merge($dropdownTables, $tables);

        return $dropdownTables;
    }

    public function getOrderByOptions()
    {
        return [
            'ASC' => 'nitm.api::lang.mapping.ascending',
            'DESC' => 'nitm.api::lang.mapping.descending',
        ];
    }
}
