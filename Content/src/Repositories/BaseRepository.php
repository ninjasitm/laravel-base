<?php

namespace Nitm\Content\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as Application;
use Nitm\Content\Traits\Repository as RepositoryTrait;
use Nitm\Content\Contracts\Repository as RepositoryContract;

abstract class BaseRepository implements RepositoryContract
{
    use RepositoryTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @inheritDoc
     */
    public function getFieldsSearchable(): array
    {
        return $this->model->getFillable();
    }
}