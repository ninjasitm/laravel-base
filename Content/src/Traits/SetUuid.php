<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Str;
use Nitm\Content\Database\Eloquent\UuidBuilder;

trait SetUuid
{
    public static function bootSetUuid()
    {
        static::creating(
            function ($model) {
                if (!property_exists($model, 'uuidFields')) {
                    return;
                }
                if (!isset($model->uuidFields)) {
                    return;
                }

                foreach ((array)$model->uuidFields as $field) {
                    $model->$field = $model->$field ?? Str::uuid();
                }
            }
        );
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new UuidBuilder($query);
    }
}