<?php

namespace Nitm\Content\Notifications;

use Nitm\Content\Models\ChatMessage;
use Illuminate\Bus\Queueable;
use Nitm\Content\Notifications\FcmFunctionChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidMessagePriority;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use Nitm\Content\Notifications\BaseNotification as Notification;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NotifyOfNewChatMessageByFirebase extends Notification
{
    use Queueable;

    /**
     * The thread the message was created on
     *
     * @var string
     */
    public $title;

    /**
     * The message
     *
     * @var ChatMessage
     */
    public $message;

    /**
     * The url
     *
     * @var string
     */
    public $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $title, ChatMessage $message, string $url = null)
    {
        //
        $this->title = $title;
        $this->message = $message;
        $this->url = $url ?? config('app.webUrl') . '/app/chat';
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
            ->setData($this->toArray())
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
        return [
            'id' => $this->message->id . "",
            'action' => 'new-message',
            'type' => 'message',
            'date' => $this->message->date,
            'title' => $this->message->thread->title ?? $this->message->to->name ?? " New message",
            'thread_id' => $this->message->thread_id,
            'user_id' => $this->message->user_id . "",
            'to_id' => $this->message->to_id . "",
            'body' => $this->message->message
        ];
    }
}