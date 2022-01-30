<?php

namespace Nitm\Content\Listeners;

use Nitm\Content\Team;
use Nitm\Content\Models\User;
use Nitm\Content\Events\NotifyUser;
use Nitm\Content\Events\NotifyUsers;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Events\NotifyAdmins;
use Illuminate\Support\Optional;
use Illuminate\Support\Collection;
use Nitm\Content\Events\BaseAutomationEvent;
use Nitm\Content\Contracts\ListensToActivity;
use Illuminate\Database\Eloquent\Model;
use Nitm\Content\Events\Automation\CheckForAutomation;
use Nitm\Content\Contracts\Listeners\ListensToAutomationEvents;

abstract class BaseAutomationEventListener implements ListensToAutomationEvents
{
    /**
     * The notification class
     *
     * @var string
     */
    protected $notificationClass;

    /**
     * The notification class
     *
     * @var string
     */
    protected $ownerNotificationClass;

    /**
     * The class to use for sending an admin notification
     *
     * @var string
     */
    protected $adminNotifictationClass;

    /**
     * Get the messge for the user
     *
     * @var string
     */
    protected $message;

    /**
     * Get the message for the admin
     *
     * @var string
     */
    protected $adminMessage;

    /**
     * Get the messge for the user
     *
     * @var string
     */
    protected $actionText = 'View';

    /**
     * Get the message for the admin
     *
     * @var string
     */
    protected $adminActionText = 'View';

    /**
     * Get the property used to extract the model property from the event
     *
     * @var string
     */
    protected $subjectProperty = 'model';

    /**
     * Get the property used to extract the subject property from the subject or event
     *
     * @var string
     */
    protected $actorProperty = 'user';


    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     */
    public function handle($event)
    {
        // $event->model->load('program');
        /**
         * Notify program members
         */
        //TODO: Check to see whether the given listener is enabled for the current user
        $team = $this->getTeam($event);
        if ($team && $team->admins()->count() && ($notification = $this->getAdminNotification($event))) {
            event(
                new NotifyAdmins(
                    $team,
                    $this->getMessageDataForAdmin($team, $event),
                    $this->getDataForAdmin($team, $event),
                    $notification
                )
            );
        }

        // $user = $this->getOwner($event);
        // if ($user && !$user->isAdminOn($team) && ($notification = $this->getNotification($event))) {
        //     event(
        //         new NotifyUser(
        //             $team,
        //             $user,
        //             $this->getMessageData($team, $event),
        //             $this->getData($team, $event),
        //             $notification
        //         )
        //     );
        // }

        $users = $this->getUsers($event);
        if ($users && $users->count() && ($notification = $this->getNotification($event))) {
            event(
                new NotifyUsers(
                    $users,
                    $this->getMessageData($event),
                    $this->getData($event),
                    $notification
                )
            );
        }

        $users = $this->getOwner($event);
        if ($users && $users->count() && ($notification = $this->getOwnerNotification($event))) {
            event(
                new NotifyUsers(
                    $users,
                    $this->getMessageDataForOwner($event),
                    $this->getData($event),
                    $notification
                )
            );
        }

        // Disabling automations here
        // event(new CheckForAutomation($event, $event->model, $this->getTeam($event)));
    }

    /**
     * Get Admin Notifier
     *
     * @return void
     */
    public function getAdminNotificationClass()
    {
        return $this->adminNotificationClass;
    }

    /**
     * Get Owner Notifier
     *
     * @return void
     */
    public function getOwnerNotificationClass()
    {
        return $this->ownerNotificationClass;
    }

    /**
     * Get User Notifier
     *
     * @return void
     */
    public function getNotificationClass()
    {
        return $this->notificationClass;
    }

    /**
     * Get the Admin Notification
     *
     * @return Model
     */
    public function getAdminNotification($event)
    {
        if (isset($this->adminNotificationClass) && class_exists($this->adminNotificationClass)) {
            $class = $this->adminNotificationClass;
            return (new $class(...$this->getEventContructParams($event)))->setData($this->getData($event));
        }
    }

    /**
     * Get the Notification
     *
     * @return Model
     */
    public function getNotification($event)
    {
        if (isset($this->notificationClass) && class_exists($this->notificationClass)) {
            $class = $this->notificationClass;
            return (new $class(...$this->getEventContructParams($event)))->setData($this->getData($event));
        }
    }

    /**
     * Get the Notification for the owner of the content
     *
     * @return Model
     */
    public function getOwnerNotification($event)
    {
        if (isset($this->ownerNotificationClass) && class_exists($this->ownerNotificationClass)) {
            $class = $this->ownerNotificationClass;
            return (new $class(...$this->getEventContructParams($event)))->setData($this->getData($event));
        }
    }

    /**
     * Get Event Contruct Params
     *
     * @param  mixed $event
     * @return array
     */
    public function getEventContructParams($event): array
    {
        if ($event instanceof AutomationEvent) {
            return $event->getConstructParams();
        } else if ($this->hasEventConstructorMappingFor(get_class($event))) {
            return $this->extractEventConstructorParamatersFor(get_class($event))($event);
        }
        return [];
    }

    /**
     * Has Event Constructor Mappting For the given class
     *
     * @param  mixed $event
     * @return bool
     */
    protected function hasEventConstructorMappingFor(string $event): bool
    {
        return Arr::has($this->getEventConstructorMappings(), $event);
    }

    /**
     * hasEventConstructorMapptingFor
     *
     * @param  mixed $event
     * @return callable
     */
    protected function extractEventConstructorParamatersFor(string $event): callable
    {
        return Arr::get($this->getEventConstructorMappings(), $event, function () {
            return [];
        });
    }

    /**
     * Get Event Constructor Mappings
     *
     * @return array
     */
    protected function getEventConstructorMappings(): array
    {
        return [];
    }

    /**
     * Get the user from the event
     *
     * @param  mixed $event
     * @return Team
     */
    protected function getTeam($event)
    {
        return null;
    }

    /**
     * Get the user from the event
     *
     * @param  mixed $event
     * @return User
     */
    protected function getOwner($event)
    {
        if (property_exists($event, 'model') && property_exists($event->model, 'user')) {
            return collect([$event->model->user]);
        }
        return collect([]);
    }

    /**
     * Get the user from the event
     *
     * @param  mixed $event
     * @return Collection
     */
    protected function getUsers($event): Collection
    {
        return collect([]);
    }

    /**
     * Get The Type for the notification
     *
     * @return void
     */
    protected function getType()
    {
        return Str::slug(class_basename(get_class($this)));
    }

    /**
     * Get the Core Data
     *
     * @param  mixed $team
     * @return array
     */
    protected function getCoreData($event): array
    {
        $subject = $this->getSubject($event);
        $actor = $this->getActor($event);
        return [
            'type' => $this->getType(),
            'item' => [
                'id' => $subject->id,
                'title' => $subject->title
            ],
            'actor' => [
                'id' => $actor->username,
                'name' => $actor->name,
                'image' => $actor->profile_photo_path
            ],
        ];
    }

    /**
     * Get the Team Data
     *
     * @param  mixed $team
     * @return array
     */
    protected function getTeamData(Team $team): array
    {
        return array_merge($this->getCoreData($event), [
            'team' => [
                'id' => $team->id,
                'slug' => $team->slug,
                'name' => $team->name
            ],
        ]);
    }

    /**
     * Get the event Message
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getMessage($event): string
    {
        return $this->message;
    }

    /**
     * Get the Action Text
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionText($event): string
    {
        return $this->actionText;
    }

    /**
     * Get Message Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageData($event): array
    {
        return [
            'message' => $this->getMessage($event),
            'params' => array_merge([
                'type' => $this->getType(),
            ], $this->getMessageParams($event))
        ];
    }

    /**
     * Get the Message For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getMessageForAdmin($event): string
    {
        return $this->adminMessage;
    }

    /**
     * Get the Message For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getMessageForOwner($event): string
    {
        return $this->ownerMessage;
    }

    /**
     * Get Message Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageDataForOwner($event): array
    {
        return array_merge($this->getMessageData($event), [
            'message' => $this->getMessageForOwner($event)
        ]);
    }

    /**
     * Get the Action Text For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionTextForAdmin($event): string
    {
        return $this->adminActionText;
    }

    /**
     * Get Message Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageDataForAdmin($event): array
    {
        return array_merge($this->getMessageData($event), [
            'message' => $this->getMessageForAdmin($event)
        ]);
    }

    /**
     * Get Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getDataForAdmin($event): array
    {
        return array_merge($this->getData($event), [
            'action_text' => $this->getActionTextForAdmin($event),
            'action_url' => $this->getActionUrlForAdmin($event)
        ]);
    }

    /**
     * Get the Params For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageParamsForAdmin($event): array
    {
        return $this->getMessgeParams($event);
    }


    /**
     * Get Data
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getData($event): array
    {
        return array_merge([
            'id' => $event->model->id,
            'title' => $event->model->title,
            'action_text' => $this->getActionText($event),
            'action_url' => $this->getActionUrl($event)
        ]);
    }

    /**
     * Get Subject
     *
     * @return void
     */
    public function getSubject($event): Model|Optional
    {
        return optional(data_get($event, $this->subjectProperty));
    }

    /**
     * Get Subject
     *
     * @return void
     */
    public function getActor($event): Model|Optional
    {
        return optional(data_get($event, $this->actorProperty)) ?? data_get($this->getSubject($event), $this->actorProperty);
    }

    /**
     * Get the event Params
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return array
     */
    public function getMessageParams($event): array
    {
        return [
            'itemName' => $event->model->title,
            'userName' => $event->model->user->name
        ];
    }

    /**
     * Get the Action Url
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionUrl($event): string
    {
        return '/' . implode('/', ['app', $event->model->is, $event->model->id]);
    }

    /**
     * Get the Action Url For Admin
     *
     * @param  mixed $team
     * @param  mixed $event
     * @return string
     */
    public function getActionUrlForAdmin($event): string
    {
        return '/' . implode('/', ['app', $event->model->is, $event->model->id]);
    }
}