<?php

namespace Nitm\Content\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Repository
{
    /**
     * Sync the model's data
     *
     * @param array $data
     */
    public function syncData(Model $model, array $data);

    /**
     * Get searchable fields array
     *
     * @return array
     */
    public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    public function model(): string;

    /**
     * Import models
     *
     * @return array
     */
    public function import(array $data): array;
}