<?php

namespace Nitm\Content\Contracts\Automation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

interface EventHandler
{

    /**
     * Handle this specific automation
     *
     * @param \App\Models\Automation\Automation $automation
     * @param object $event The event that triggered this automation event
     * @param object|null $model The model that initiated this automation
     */
    public function handle($automation, $event, $model = null);

    /**
     * Wrap up the handling of an automation
     * Should insert an automation activity into the database
     * TODO: Implement business logic for tracking number of automations run
     *
     * @param \App\Models\Automation\Automation $automation
     * @param object|null $model The model that initiated this automation
     *
     * @return void
     */
    public function finish($automation, $event = null);
}
