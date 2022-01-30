<?php

namespace Nitm\Content\Contracts\Listeners;

use App\Events\BaseAutomationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

interface ListensToAutomationEvents extends ShouldQueue
{

    /**
     * Get Message Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageData($event): array;

    /**
     * Get Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getData($event): array;

    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     */
    public function handle($event);


    /**
     * Get the event Params
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageParams($event): array;

    /**
     * Get the event Message
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getMessage($event): string;

    /**
     * Get the Action Text
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionText($event): string;

    /**
     * Get the Action Url
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionUrl($event): string;

    /**
     * Get Message Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageDataForAdmin($event): array;

    /**
     * Get Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getDataForAdmin($event): array;

    /**
     * Get the Params For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageParamsForAdmin($event): array;

    /**
     * Get the Message For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getMessageForAdmin($event): string;

    /**
     * Get the Action Text For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionTextForAdmin($event): string;

    /**
     * Get the Action Url For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionUrlForAdmin($event): string;
}
