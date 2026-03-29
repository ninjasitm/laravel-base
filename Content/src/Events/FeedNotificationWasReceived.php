<?php
namespace Nitm\Content\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Nitm\Content\Contracts\Repositories\NotificationRepository as NotificationRepositoryContract;
use Nitm\Content\NitmContent;
use Nitm\Helpers\CollectionHelper;

class FeedNotificationWasReceived implements ShouldBroadcast {
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public array $data;

    public $user;

    public $users;

    public function __construct(array $data, $users) {
        $this->data  = $data;
        $this->user  = $users;
        $this->users = $users;
    }

    public function broadcastOn(): array {
        return $this->normalizeUsers()
            ->map(function ($user) {
                $userId = is_object($user) ? ($user->id ?? null) : $user;

                return $userId ? new PrivateChannel('users.' . $userId) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function broadcastWith(): array {
        return $this->data;
    }

    public function persistManyNotifications(array $data): void {
        $repository = app(NotificationRepositoryContract::class);

        $this->normalizeUsers()
            ->map(function ($user) {
                return $this->resolveUser($user);
            })
            ->filter(function ($user) {
                return is_object($user) && isset($user->id);
            })
            ->each(function ($user) use ($repository, $data) {
                $repository->create($user, $data);
            });
    }

    protected function normalizeUsers(): Collection {
        if (CollectionHelper::isCollection($this->users)) {
            return $this->users;
        }

        return collect(is_array($this->users) ? $this->users : [$this->users]);
    }

    protected function resolveUser($user) {
        if (CollectionHelper::isCollection($user)) {
            $user = $user->first();
        }

        if (is_object($user)) {
            return $user;
        }

        $userModel = NitmContent::userModel();

        return class_exists($userModel) ? $userModel::find($user) : null;
    }
}