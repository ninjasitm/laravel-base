<?php

namespace Nitm\Content\Contracts\Automation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

interface AutomationCommand
{

    /**
     * Handle this specific automation
     *
     * @param \App\Models\Automation\Automation $automation
     * @param object $event The event that triggered this automation event
     * @param object|null $model The model that initiated this automation
     */
    public function handle();

    /**
     * Validate a trigger
     *
     * @param \App\Models\Automation\Trigger $trigger
     *
     * @return boolean
     */
    public function validate($trigger);
}