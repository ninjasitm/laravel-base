<?php

namespace Nitm\Content\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Nitm\Helpers\ClassHelper;

class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();
        if (ClassHelper::hasTrait($this->resource, 'Nitm\Content\Traits\SetUuid')) {
            $result['id'] = $this->resource->uuid;
        }

        if (ClassHelper::hasTrait($this->resource, 'Nitm\Content\Traits\SupportsHashIds')) {
            $result['id'] = $this->resource->hashId;
        }

        return $result;
    }
}
