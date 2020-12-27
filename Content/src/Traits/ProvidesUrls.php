<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ProvidesUrls
{
    /**
     * Provide the public URL to the model
     */
    public function getPublicUrl($parts = [])
    {
        return implode('/', array_merge([config('app.webUrl')], $parts));
    }
}
