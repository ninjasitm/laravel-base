<?php

namespace Nitm\Content\Contracts;
interface NotifiesOfActivity
{

    /**
     * Prepare an event by collecting data
     */
    public function prepare($event);
}
