<?php

namespace Nitm\Content\Models;

use Illuminate\Support\Arr;
use Nitm\Content\Traits\Search;
use Nitm\Content\Traits\HasFiles;
use Nitm\Content\Traits\SetUserId;
use Nitm\Content\Traits\CustomWith;
use Nitm\Content\Traits\HasMetadata;
use Nitm\Content\Traits\FiltersUsers;
use Nitm\Content\Traits\ProvidesUrls;
use Nitm\Content\Traits\FiltersModels;
use Nitm\Content\Traits\HasTimestamps;
use Nitm\Content\Traits\SyncsRelations;
use Nitm\Content\Traits\Model as ModelTrait;
use Nitm\Content\Traits\DatesTimezoneConversion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

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

    /**
     * Fields visible to the API
     *
     * @var mixed
     */
    protected $visibleToApi;

    public function fromJson($value, $asObject = false)
    {
        return is_array($value) || is_object($value) ? $value : json_decode($value, !$asObject);
    }

    /**
     * If the `` property is set, return only those properties, otherwise return all
     * properties
     *
     * @return The result of the parent::toArray() method, but only the fields that are specified in
     * the  property.
     */
    public function toArray()
    {
        $result = parent::toArray();
        return !empty($only = $this->visibleToApi)  ? Arr::only($result, (array) $only) : $result;
    }
}
