<?php

namespace Nitm\Content\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Repositories\FileRepository;

class FileUploadController extends Controller
{
    protected $model;

    protected $availableModels = [];

    /**
     * @inheritDoc
     */
    public function repository()
    {
        return FileRepository::class;
    }

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // $this->middleware(['auth:api']);

        $this->validateEntity($request->route('entity'));
    }

    /**
     * @param Request $request
     * @param mixed $entity
     * @param mixed $entityId
     *
     * @return [type]
     */
    public function store(Request $request, $entity, $entityId)
    {
        $entity = lcfirst(Str::studly($entity));

        $model = $team->$entity()->findOrFail($entityId);

        return $this->printSuccess($model->syncFiles($request->all()));
    }

    /**
     * @param Request $request
     * @param mixed $entity
     * @param mixed $entityId
     * @param mixed $id
     *
     * @return [type]
     */
    public function show(Request $request, $entity, $entityId, $id)
    {
        $entity = lcfirst(Str::studly($entity));

        $model = $team->$entity()->findOrFail($entityId)->allFiles()->findOrFail($id);

        return $this->printSuccess($model->fresh());
    }

    /**
     * @param Request $request
     * @param mixed $entity
     * @param mixed $entityId
     * @param mixed $id
     *
     * @return [type]
     */
    public function destroy(Request $request, $entity, $entityId, $id)
    {
        $entity = lcfirst(Str::studly($entity));

        $model = $team->$entity()->findOrFail($entityId)->allFiles()->findOrFail($id);

        return $this->printSuccess($model->delete());
    }

    /**
     * @param mixed $entity
     *
     * @return void
     */
    private function validateEntity($entity = null): void
    {
        if (!empty($this->availableModels) && $entity && array_search($entity, $this->availableModels) === false) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Entity not found');
        }
    }
}