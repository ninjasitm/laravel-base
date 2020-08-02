<?php

namespace Nitm\Content\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Models\Metadata\Metadata;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class MetadataRepository
 * @package Nitm\Content\Repositories\Metadata
 * @version October 24, 2019, 10:51 pm UTC
 */

class MetadataRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'entity_type',
        'entity_id',
        'name',
        'type',
        'value',
        'priority',
        'entity_relation',
        'options',
        'linked_metadata_id',
        'is_required',
        'section'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Metadata::class;
    }
}
