<?php

namespace Nitm\Content\Models;

class RelatedTeam extends Team
{
    protected $table = 'teams';

    protected $with = ['owner'];

    protected $withCount = [];

    protected $customWith = [];

    protected $customWithCount = [];

    protected $visible = [
        'id', 'name', 'photo_url',
        'website', 'owner_id', 'slug',
        'pivot', 'owner', 'role', 'feature_names',
        'timezone', 'date_format', 'time_format'
    ];

    /**
     * Overriding the toArray method of the team so as to not call extra DB methods when loading related Team model
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
}