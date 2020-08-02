<?php

namespace Nitm\Api\Controllers;

use October\Rain\Support\Facades\Flash;
use Backend\Facades\BackendMenu;
use Backend\Classes\Controller;
use Nitm\Api\Models\Mapping;
use Nitm\Api\Classes\Trivet;

class Mappings extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $requiredPermissions = ['nitm.api.mappings'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $bodyClass = 'compact-container breadcrumb-flush';

    public function __construct($tab = null)
    {
        parent::__construct();
        BackendMenu::setContext('Nitm.Restful', 'api', 'mappings');
    }

    public function index()
    {
        $this->getClassExtension('Backend.Behaviors.ListController')->index();
    }

    /**
     * Install new plugins / themes.
     *
     * @param null $tab
     */
    public function section($tab = null)
    {
        $this->vars['activeTab'] = $tab ? $tab : 'automation';
    }

    /*
    public function listExtendColumns($list)
    {
        $list->addColumns([
            'id' => '# id'
        ]);
    }
    */

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $relationId) {
                if (!$role = Mapping::find($relationId)) {
                    continue;
                }

                $role->delete();
            }

            Flash::success('Relation has been deleted successfully.');
        }

        return $this->listRefresh();
    }

    public function onTableChange()
    {
        $table = post('Mapping.relatedtable');
        $this->vars['dbFields'] = Trivet::getFieldsFromDbOrClass($table, true);

        return $this->vars['dbFields'];
    }
}
