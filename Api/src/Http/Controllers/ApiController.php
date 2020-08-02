<?php

namespace Nitm\Api\Controllers;

use Nitm\Api\Classes\Trivet;

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
class ApiController extends BaseApiController
{
    /**
    * Define methods for operations
    * 'do parameter' => 'method name'.
    */
   public $operations = [
       'create' => 'create',
       'read_all' => 'readAll',
       'read' => 'read',
       'update' => 'update',
       'delete' => 'delete',
   ];
   /**
    * Parameters expected from GET/POST for each CRUDs
    * mandatory    => Exact needed parameters
    * purge        => Deletable before doing Ops
    * optional     => Optional for defining parameters.
    */
   public $fields = [
       'create' => [
           'mandatory' => ['do', 'req'],
           'purge' => ['type'],
           'optional' => ['auth'],
       ],
       'read_all' => [
           'mandatory' => ['do', 'req'],
           'purge' => ['type'],
           'optional' => ['auth'],
       ],
       'read' => [
           'mandatory' => ['do', 'req'],
           'purge' => ['type'],
           'optional' => ['auth', 'key'],
       ],
       'update' => [
           'mandatory' => ['do', 'req'],
           'purge' => ['type'],
           'optional' => ['auth'],
       ],
       'delete' => [
           'mandatory' => ['do', 'req'],
           'purge' => ['type'],
           'optional' => ['auth'],
       ],
   ];

    public function options()
    {
        return true;
    }

   /**
    * Use this endpoint to create data on the OctopusArtworks API. The API expects.
    * POST /{req}.
    *
    * @param string $req The request [art..etc]
    *
    * @return Response
    */
   public function create($req = null)
   {
       return $this->getResult(function () use ($req) {
           $req = $req ?: Trivet::getInputs('req');
           /*
           * Start "Creater" class operation
           */
           $helper = new $this->helperClasses['create']();
           $helper->passData($this->fields['create'], $this->request_parameters, $this->all_relations);
           $helper->buildColumnFields();

         /* Make relation based creating before direct table access control */
           if (in_array($req, $this->request_parameters)) {
               $helper->makeRelationBasedCreate();
           } else {
               $helper->makeDirectTableCreate();
           }

           return $helper->data;
       });
   }

   /**
    * Get a list of data for a particular data type. Limit of 20 items by default
    * GET /{req}.
    *    *.
    *
    * @param string $req The request [art..etc]
    *
    * @return Response
    */
   public function readAll($req = null)
   {
       return $this->getResult(function () use ($req) {
           $req = $req ?: Trivet::getInputs('req');
          /*
           * Start "ReaderAll" class operation
           */
           $helper = new $this->helperClasses['read_all']();
           $helper->passData($this->all_relations, $this->request_parameters);
           $helper->defineResultLimit();

         /* Check & fetch relation based all datas before direct table access control */
         if (in_array($req, $this->request_parameters)) {
             $helper->makeRelationBasedRead();
         } else {
             $helper->makeDirectTableRead();
         }

           return $helper->lastControl();
       });
   }

   /**
    * Get a single entry for a data type specified by {id}
    * GET /req/{id}.
    *
    * @param string $req The request [art..etc]
    * @param int    $id  The id of the data type to get
    *
    * @return Response
    */
   public function read($req = '', $id = null)
   {
       return $this->getResult(function () use ($req, $id) {
           $req = $req ?: Trivet::getInputs('req');
           /*
           * Start "Reader" class operation
           */
           $helper = new $this->helperClasses['read']();
           $helper->passData($this->fields['read'], $this->all_relations, $this->request_parameters, $this->where_index_key);
           $helper->defineResultLimit();

         /* Check & fetch relation based data before direct table access control */
         if (in_array($req, $this->request_parameters)) {
             $helper->makeRelationBasedRead();
         } else {
             $helper->makeDirectTableRead();
         }

           return $helper->lastControl();
       });
   }

   /**
    * Update the data types specified by {req} and optional {id}.
    * POST /req/{id?}.
    *
    * @param string $req The request [art..etc]
    * @param int    $id  The optional id of the data type to get
    *
    * @return Response
    */
   public function update($req = '', $id = null)
   {
       return $this->getResult(function () use ($req, $id) {
           $req = $req ?: Trivet::getInputs('req');
           /*
           * Start "Updater" class operation
           */
           $helper = new $this->helperClasses['update']();
           $helper->passData($this->fields['update'], $this->all_relations, $this->request_parameters, $this->where_index_key);
           $helper->buildColumnFields();

            /* Update relation based data before direct table access control */
            if (in_array($req, $this->request_parameters)) {
                $helper->makeRelationBasedUpdate();
            } else {
                $helper->makeDirectTableUpdate();
            }

           return $helper->lastControl();
       });
   }

   /**
    * Delete one row (maybe more by statement) from table.
    * DELETE /req/{id}.
    *
    * @param string $req The request [art..etc]
    * @param int    $id  The id of the data type to delete
    *
    * @return Response
    */
   public function delete($req = '', $id = null)
   {
       return $this->getResult(function () {
           $req = $req ?? Trivet::getInputs('req');
           /*
           * Start "Deleter" class operation
           */
           $helper = new $this->helperClasses['delete']();
           $helper->passData($this->all_relations, $this->request_parameters, $this->where_index_key);

            /* Delete relation based data before direct table access control */
            if (in_array($req, $this->request_parameters)) {
                $helper->makeRelationBasedDelete();
            } else {
                $helper->makeDirectTableDelete();
            }
           Trivet::addViewLog('delete', [
              'content_type' => $req,
              'content_id' => $this->where_index_key,

           ]);

           return $helper->lastControl();
       });
   }
}
