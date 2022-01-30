<?php

namespace Nitm\Content\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use NotificationChannels\Fcm\FcmMessage;
use Nitm\Content\Notifications\FcmFunctionChannel;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use Nitm\Content\Notifications\BaseNotification as Notification;
use NotificationChannels\Fcm\Resources\AndroidMessagePriority;

class NotifyOfNewFeedNotificationByFirebase extends Notification
{
    use Queueable;

    /**
     * The notification action
     *
     * @var string
     */
    public $action;

    /**
     * The notification title
     *
     * @var string
     */
    public $title;

    /**
     * The notification message
     *
     * @var string
     */
    public $message;

    /**
     * The data
     *
     * @var array
     */
    public $data;

    /**
     * The url
     *
     * @var string
     */
    public $url;

    /**
     * The icon
     *
     * @var string
     */
    public $icon;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->action = Arr::get($data, 'action') ?? Arr::get($data, 'action_text') ?? 'new_notification';
        $this->message = Arr::get($data, 'message') ?? Arr::get($data, 'body');
        $this->title = Arr::get($data, 'title');
        $this->data = $data;
        $this->url = Arr::get($data, 'url') ?? Arr::get($data, 'action_url');
        $this->icon = Arr::get($data, 'icon') ?? Arr::get($data, 'image');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmFunctionChannel::class];
    }

    /**
     * Get the firebase notification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(
                array_merge(
                    [
                        'action' => $this->action,
                        'type' => 'new_notification'
                    ],
                    $this->data
                )
            )
            // ->setNotification(
            //     FcmNotification::create()
            //         ->setTitle('New message was received in ' . $this->title)
            //         ->setBody($this->message->message)
            //         ->setImage($this->message->user->photo_url)
            // )
            ->setAndroid(
                AndroidConfig::create()
                    ->setPriority(AndroidMessagePriority::NORMAL())
                    ->setCollapseKey('com.wethrive')
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics'))
            )
            ->setApns(
                ApnsConfig::create()
                    ->setHeaders([
                        'apns-push-type' => 'alert',
                        'apns-priority' => '10',
                        // 'apns-expiration' => now('UTC')->addMinutes(30)->timestamp,
                        'apns-collapse-id' => 'com.wethrivetech.wethrive',
                        'apns-topic' => 'com.wethrivetech.wethrive'
                    ])
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable = null)
    {
        $data = array_merge([
            'title' => $this->title,
            'body' => $this->message,
            'action' => $this->action,
            'url' => $this->url,
            'icon' => $this->icon,
        ], $this->data);

        return array_map(function ($v) {
            if (is_array($v)) {
                $v = json_encode($v);
            }
            return $v . "";
        }, $data);
    }
}