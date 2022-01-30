<?php

namespace Nitm\Content\Events;

use Nitm\Content\Traits\SupportsAutomation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Nitm\Content\Contracts\Automation\Event as AutomationEvent;

abstract class BaseAutomationEvent implements AutomationEvent, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels, SupportsAutomation, InteractsWithQueue;

    /**
     * Model
     *
     * @var Model
     */
    public $model;

    /**
     * The event description
     *
     * @var string
     */
    public $description = "No description available";

    /**
     * The default message send for this event. Used primarily for automations with messages
     *
     * @var string
     */
    public $defaultMessage = "No message available";

    public static function getVariables()
    {
        return [];
    }

    public function __($message = null)
    {
        return static::prepareMessage($message);
    }
}