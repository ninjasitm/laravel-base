<?php

namespace App\Repositories;

use Nitm\Content\Repositories\BaseRepository as AbstractBaseRepository;

abstract class BaseRepository extends AbstractBaseRepository
{
    /**
     * @inheritDoc
     */
    public function getFieldsSearchable(): array
    {
        return $this->model->getFillable();
    }
}