<?php

namespace Nitm\Content\Notifications;

use GuzzleHttp\Client;
use Nitm\Helpers\ModelHelper;
use GuzzleHttp\RequestOptions;
use Nitm\Helpers\CollectionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\Exceptions\CouldNotSendNotification;

class FcmFunctionChannel
{
    const MAX_TOKEN_PER_REQUEST = 500;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return array
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the message from the notification class
        $fcmMessage = (array)$notification->toArray($notifiable);
        \Log::info("Sending notification: " . get_class($notification) . ":" . json_encode($fcmMessage, JSON_PRETTY_PRINT));
        $result = array_merge([
            'type' => 'fcm-notification',
            'team' => [
                'id' => null,
                'slug' => null,
                'name' => null
            ],
            'item' => [
                "id" => null,
                "title" => null
            ],
            "action_text" => null,
            "action_url" => null,
            "actor" => [],
        ], $fcmMessage);

        $url = config('services.firebase.function_url');
        if (!$url) {
            if ((app()->environment('dev') || app()->environment('local')) && env('FIREBASE_FCM_DIE_ON_ERROR', false)) {
                throw new \Exception("No url specified for FCM function");
            }
            \Log::warning("No url specified for FCM function");
            return $result;
        }

        $token = $notifiable->routeNotificationFor('fcmFunction', $notification);

        if (empty($token)) {
            \Log::warning("No token provided for [" . implode("|", [$notifiable->id, $notifiable->username]) . "]");
            return $result;
        }

        \Log::info("Firebase Notification via: $url for: [" . implode("|", [$notifiable->id, $notifiable->username]) . "]");

        $token = CollectionHelper::isCollection($token) ? $token : collect((array)$token);
        $token = $token->filter();
        if ($token->count()) {
            $client = new Client([
                'timeout' => env('FIREBASE_NOTIFICATION_TIMEOUT', 30)
            ]);

            try {
                $this->payload = array_merge($result, ['tokens' => $token->toArray()]);
                $response = $client->post($url, [
                    RequestOptions::JSON => $this->payload
                ]);
                $response = $response->getBody()->getContents();
                $response = true == ModelHelper::boolval($result) ? $this->payload : $response;
            } catch (\Exception $e) {
                \Log::error($e);
                if ((app()->environment('dev') || app()->environment('local')) && env('FIREBASE_FCM_DIE_ON_ERROR', false)) {
                    throw $e;
                }
            }
            return $response === 'ok' ? array_merge(['type' => 'fcm-notification'], $result) : ['success' => false];
        }
        return $result;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable = null)
    {
        return [
            'type' => 'fcm-notification',
            'action' => 'fcp-notify',
            'message' => 'FCM Message was sent',
            'url' => '',
            'data' => []
        ];
    }
}