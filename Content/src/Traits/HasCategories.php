<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Nitm\Content\NitmContent;

trait HasCategories
{
    /**
     * Sync the avatar for a user
     * @param array $data
     */
    public function categories()
    {
        return $this->morphToMany(NitmContent::categoryModel(), 'entity', 'attached_categories');
    }
}