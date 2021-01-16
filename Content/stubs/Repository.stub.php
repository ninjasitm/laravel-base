<?php

namespace App\Repositories;

use Nitm\Content\Repositories\BaseRepository;

class Repository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    public function getFieldsSearchable(): array
    {
        return $this->model->getFillable();
    }
}