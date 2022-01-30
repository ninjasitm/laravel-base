<?php

namespace Nitm\Content\Listeners;

use Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Helpers\CollectionHelper;
use Nitm\Content\Contracts\NotifiesOfActivity;
use Nitm\Content\Notifications\BaseNotification;
use Nitm\Content\Events\FeedNotificationWasReceived;

abstract class BaseNotifyOfActivity implements NotifiesOfActivity
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $data = [];
        $params = $this->prepare($event);
        if (is_array($params)) {
            extract($params);
            if (
                isset($users)
                && (CollectionHelper::isCollection($users))
                && $users->count()
            ) {
                $users = $users->unique('id');
                // Ensure empty data isn't sent
                $data = array_filter($data);
                $event->data = array_filter($event->data);
                $data = array_merge([
                    'icon' => 'new_releases',
                    'title' => Arr::get($event->data, 'item_title'),
                    'type' => Str::slug(class_basename(get_class($event))),
                    'body' => $this->prepareMessage($event->message, $event->messageParams)
                ], $event->data, $data);

                $feedNotification = new FeedNotificationWasReceived($data, $users);

                /**
                 * Using a persisting function to avoid creating multiple notifications/db inserts and multiple events
                 */
                $feedNotification->persistManyNotifications($data);
                \Log::info(serialize($feedNotification));
                broadcast($feedNotification)->toOthers();
                if (property_exists($event, 'mailable') && $event->mailable) {
                    if ($event->mailable instanceof BaseNotification) {
                        $event->mailable->setData([
                            "message" => Arr::get($data, 'body', Arr::get($data, 'message')) ?? Arr::get($data, 'message'),
                            'action' => Arr::get($data, 'action_text'),
                            'url' => Arr::get($data, 'action_url')
                        ]);
                    }
                    Notification::send($users, $event->mailable);
                }
            }
        }
    }

    /**
     * Advanced message creation supporting collection messages
     *
     * @param string $message
     * @param array $params
     * @return void
     */
    public static function prepareMessage(string $message, array $params = [])
    {
        if (!Arr::get($params, 'subMessages')) {
            return strlen($message) ? __($message, Arr::dot($params))  : $message;
        } else {
            $subMessageParams = Arr::pull($params, 'subMessages');
            $collection = Arr::pull($subMessageParams, 'collection');
            $collection = CollectionHelper::isCollection($collection) ? $collection : collect($collection);
            $subMessage = Arr::get($subMessageParams, 'message');
            $subMessageParams = Arr::get($subMessageParams, 'params');
            /**
             * TODO: Figure out why these aren't firing
             */
            $subMessages = $collection->reduce(function ($carry, $item) use ($subMessage, $subMessageParams) {
                $localParams = [];
                foreach ($subMessageParams as $key => $property) {
                    if (is_callable($property)) {
                        $value = $property($item);
                    } else {
                        $arrayItem = is_array($item) ? $item : $item->toArray();
                        $value = Arr::get($arrayItem, $property);
                    }
                    $localParams[$key] = $value;
                }
                $carry[] = __($subMessage, $localParams);
                return $carry;
            }, []);
            $params['subMessages'] = implode($subMessages, "\n");
            return __($message, $params);
        }
    }
}