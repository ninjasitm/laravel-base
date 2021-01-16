<?php

namespace Nitm\Content\Contracts;
interface ListensToActivity
{

    /**
     * Prepare an event by collecting data
     */
    public function handle($event);
}
