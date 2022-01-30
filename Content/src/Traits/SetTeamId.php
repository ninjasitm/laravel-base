<?php

namespace Nitm\Content\Traits;

trait SetTeamId
{
    public static function bootSetTeamId()
    {
        static::creating(
            function ($model) {
                if (!property_exists($model, 'createdByTeamFields')) {
                    return;
                }
                if (!isset($model->createdByTeamFields)) {
                    return;
                }

                $team = null;
                if(auth()->user()) {
                    $team = auth()->user()->team ?? auth()->user()->currentTeam;
                }

                if(!$team && request()->route()) {
                    $team = request()->route()->team;
                }

                if($team instanceof \Nitm\Content\Team) {
                    foreach ((array)$model->createdByTeamFields as $field) {
                        $model->$field = $model->$field ?? $team->id;
                    }
                }
            }
        );
    }
}