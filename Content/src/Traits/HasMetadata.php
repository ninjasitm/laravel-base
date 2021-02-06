<?php

namespace Nitm\Content\Traits;

use Nitm\Content\Models\Metadata;

trait HasMetadata
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasMetadata()
    {
        $this->addCustomWith(
            'metadata'
        );
    }

    /**
     * Delete each metadata individually
     *
     * @return integer
     */
    public function deleteMetadata()
    {
        return $this->metadata()->get()->reduce(
            function ($result, $metadata) {
                return $metadata->delete() ? $result + 1 : $result;
            }, 0
        );
    }

    public function metadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        $class = '\\Nitm\Content\\Models\\Metadata\\Metadata';
        $baseClass = class_basename(get_class($this));
        if (!class_exists('\\Nitm\Content\\Models\\Metadata\\' . $baseClass . 'Metadata')) {
            $class = Metadata::class;
        }
        return $this->morphMany($class, 'entity')
            ->where('entity_relation', 'metadata')
            ->byPriority();
    }

    public function requiredMetadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->metadata()->whereIsRequired(true);
    }

    public function missingMetadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->requiredMetadata()->isMissingValue();
    }
}