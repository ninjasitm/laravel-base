<?php

namespace Nitm\Content\Contracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

interface NotifiesOfActivity
{

    /**
     * Prepare an event by collecting data
     */
    public function prepare($event);
}
