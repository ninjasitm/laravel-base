<?php

namespace Nitm\Content\Traits;

use Nitm\Content\NitmContent;

trait SetUserId
{
    public static function bootSetUserId()
    {
        static::creating(
            function ($model) {
                if (!property_exists($model, 'createdByAuthFields')) {
                    return;
                }
                if (!isset($model->createdByAuthFields)) {
                    return;
                }

                foreach ((array)$model->createdByAuthFields as $field) {
                    $model->$field = $model->$field ?? auth()->id();
                }
            }
        );
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NitmContent::userModel(), 'user_id');
    }
}