<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;use Illuminate\Support\Str;

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
}