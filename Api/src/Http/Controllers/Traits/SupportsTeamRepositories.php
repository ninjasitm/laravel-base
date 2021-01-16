<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Api\Http\Controllers\Traits;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Nitm\Content\Repositories\BaseRepository;
use Nitm\Api\Contracts\ImplementsTeams;
use Illuminate\Database\Eloquent\Model;

trait SupportsRepositories
{
    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * Construct controller
     *
     * @param Application    $app
     * @param BaseRepository $repository
     */
    public function createRepository($repository = null)
    {
        if ($repository instanceof BaseRepository) {
            $this->repository = $repository;
        } else {
            $repositoryClass = $this->repository();
            if ($repositoryClass && class_exists($repositoryClass)) {
                $this->repository = app()->make($repositoryClass);
            }
        }
        return $this->repository;
    }

    /**
     * The repository class
     *
     * @return string
     */
    public function repository()
    {
        return null;
    }

    /**
     * Get the repository URL
     *
     * @return BaseRepository
     */
    public function getRepository()
    {
        if (!isset($this->repository)) {
            $this->repository = $this->createRepository();
        }
        return $this->repository;
    }


    public function toggle(Request $request, ImplementsTeams $team, Model $model)
    {
        $attributes = $model->toggle();

        return $this->printSuccess($attributes);
    }

    /**
     * General import method
     *
     * @param  Request         $request
     * @param  ImplementsTeams $team
     * @return void
     */
    public function import(Request $request, ImplementsTeams $team)
    {
        $result = [
            'hasError' => true,
            'message' => "Your request couldn't be completed. Please contact support for further assistance",
            'models' => []
        ];
        try {
            if (count($request->all()) > env('IMPORT_QUEUE_THRESHOLD', 25)) {
                $class = $this->getRepository()->getImportJobClass();
                $class::dispatch($this->repository(), $request, $team)->onQueue('high');
                $result = [
                    'hasError' => false,
                    'isQueued' => true,
                    'message' => "Your request is running in the background. You'll recieve a notification once it's done",
                    'models' => []
                ];
            } else {
                $result = $this->getRepository()->import($request->all(), $team)['models'];
            }
        } catch (\Exception $e) {
            if (!app()->environment('production')) {
                throw $e;
            }
            \Log::error($e);
        }
        return $this->printSuccess($result, Arr::get($result, 'message', 'ok') ?? 'ok', $result['hasError'] ? 422 : 200);
    }

    /**
     * General export method
     *
     * @param  Request         $request
     * @param  ImplementsTeams $team
     * @return void
     */
    public function export(Request $request, ImplementsTeams $team)
    {
        $result = [
            'hasError' => true,
            'message' => "Your request couldn't be completed. Please contact support for further assistance",
            'models' => []
        ];
        $class = $this->getRepository()->getExportJobClass();
        try {
            // $this->getRepository()->export($request->all(), $team);
            $class::dispatch($this->repository(), $request, $team)->onQueue('high');
            $result = [
                'hasError' => false,
                'isQueued' => true,
                'message' => "Your request is running in the background. You'll recieve a notification once it's done",
                'models' => []
            ];
        } catch (\Exception $e) {
            if (!app()->environment('production')) {
                throw $e;
            }
            \Log::error($e);
        }
        return $this->printSuccess($result, Arr::get($result, 'message', 'ok') ?? 'ok', $result['hasError'] ? 422 : 200);
    }

    /**
     * Get the configration for the index
     *
     * @param Request         $request
     * @param ImplementsTeams $team
     */
    public function indexConfig(Request $request, ImplementsTeams $team)
    {
        return $this->printSuccess($this->getRepository()->prepareIndexConfig($team, $request));
    }

    /**
     * Get the configration for the form
     *
     * @param Request         $request
     * @param ImplementsTeams $team
     */
    public function formConfig(Request $request, ImplementsTeams $team)
    {
        return $this->printSuccess($this->getRepository()->prepareFormConfig($team, $request));
    }
}