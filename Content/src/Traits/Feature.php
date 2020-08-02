<?php

namespace Nitm\Content\Traits;

use Nitm\Content\Models\Feature as FeatureModel;
use Nitm\Content\Models\FeatureType;
use Nitm\Content\MOdels\FeatureLink;

trait Feature
{
    public function getFeatureAttribute()
    {
        return $this->features ? $this->features->first() : null;
    }

    public function features()
    {
        return $this->hasManyThrough(
            FeatureModel::class,
            FeatureLink::class,
            'remote_id',
            'id',
            'id',
            'feature_id'
        )->where([
            'remote_table' => $this->getTable()
        ]);
    }

    public function featureLink()
    {
        return $this->morphOne(
            FeatureLink::class,
            'remote'
        );
    }

    /**
     * Toggle the feature status of this model
     *
     * @return bool
     */
    public function toggleFeature(): bool
    {
        if (!($this->feature instanceof FeatureModel)) {
            $type = basename(get_class($this));
            $category = FeatureType::where('title', $type)->first() ?? FeatureType::where('title', 'Showcase')->first();

            $slug = $this->slug ?? $this->title ?? $this->name;
            $where = [
                'slug' => str_slug(implode('-', [$slug, $this->id])),
                'type_id' => $category->id
            ];
            $feature = FeatureModel::where($where)->first() ?? new FeatureModel($where);

            if (!$feature->exists) {
                $title = $this->title ?? array_get($this->attributes, 'title', array_get($this->attributes, 'name'));
                $feature->fill([
                    'title' => $title,
                    'description' => $this->description ?? $title,
                ]);
                $feature->save();
            }

            $where = [
                'remote_type' => get_class($this),
                'remote_id' => $this->id
            ];
            $linkModel = FeatureLink::where($where)->first() ?? new FeatureLink($where);

            $linkModel->forceFill([
                'feature_id' => $feature->id,
                'remote_class' => get_class($this)
            ]);

            if (!$linkModel->exists) {
                $this->featureLink()->save($linkModel);
            } else {
                $linkModel->save();
            }

            $linkModel->load('feature');

            $this->setRelation('feature', $linkModel->feature);
        }

        $this->load('features');
        $this->feature->is_active = !$this->feature->is_active;
        $this->feature->save();

        return (bool) $this->feature->is_active;
    }
}
