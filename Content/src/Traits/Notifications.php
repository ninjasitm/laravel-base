<?php
namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Nitm\Content\Contracts\Repositories\NotificationRepository;
use Nitm\Content\Models\Notification;

trait Notifications {
    protected $_notifications;

    protected function notifications() {
        if (! isset($this->_notifications)) {
            $this->_notifications = app(NotificationRepository::class);
        }
        return $this->_notifications;
    }

    protected function newBroadcastNotification(array $data) {
        $userModel = config('nitm-api.user_model') ?? config('nitm-content.user_model');
        if (! class_exists($userModel)) {
            throw new \Error('Unable to find user model for trashed search middleware');
        }

        $user = Auth::user() ?: $userModel::where('email', 'admin@app.local')->first();
        $base = [];
        if ($user) {
            $base = [
                'user_id'    => Arr::get($data, 'user_id', $user->id),
                'created_by' => $user->id,
            ];
        }
        return new Notification(array_merge($base, $data));
    }
}