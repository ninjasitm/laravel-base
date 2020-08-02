<?php

namespace Nitm\Content\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Nitm\Content\Contracts\Repository as RepositoryContract;
use Nitm\Content\Traits\Repository as RepositoryTrait;

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
}