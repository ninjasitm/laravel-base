<?php

namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Nitm\Content\Traits\CustomWith;
use Nitm\Content\Traits\DatesTimezoneConversion;
use Nitm\Content\Traits\FiltersModels;
use Nitm\Content\Traits\FiltersUsers;
use Nitm\Content\Traits\HasFiles;
use Nitm\Content\Traits\HasMetadata;
use Nitm\Content\Traits\HasTimestamps;
use Nitm\Content\Traits\Model as ModelTrait;
use Nitm\Content\Traits\ProvidesUrls;
use Nitm\Content\Traits\Search;
use Nitm\Content\Traits\SetUserId;
use Nitm\Content\Traits\SyncsRelations;

class BaseModel extends EloquentModel
{
    use ModelTrait,
    HasTimestamps,
    Search,
    CustomWith,
    SyncsRelations,
    HasFiles,
    HasMetadata,
    SetUserId,
    FiltersModels,
    FiltersUsers,
    DatesTimezoneConversion,
    ProvidesUrls,
        HasFactory;

    public function fromJson($value, $asObject = false)
    {
        return is_array($value) || is_object($value) ? $value : json_decode($value, !$asObject);
    }
}
