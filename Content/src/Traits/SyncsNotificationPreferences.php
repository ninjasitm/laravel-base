<?php
namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Nitm\Content\NitmContent;

trait SyncsNotificationPreferences {
    /**
     * Sync Notification Preferences
     *
     * @param mixed $data
     * @return void
     */
    public function syncNotificationPreferences($data) {
        if (! empty($data) && (is_array($data) || $data instanceof Collection)) {
            $userModel = NitmContent::userModel();
            $teamModel = NitmContent::teamModel();
            $this->initNotificationPreferences();
            $data          = Arr::get($data, 'notification_preferences') ?: $data;
            $sanitizedData = collect([]);
            $teamId        = null;
            $userId        = null;

            if (! $this instanceof $userModel) {
                $userId = $this->id;
            }
            if (! $this instanceof $teamModel) {
                $teamId = $this->id;
            }
            foreach ($data as $k => $v) {
                $v = array_merge($v, [
                    'user_id' => $userId,
                    'team_id' => $teamId,
                ]);
                $sanitizedData[$k] = $v;
            }
            $this->syncRelation($sanitizedData, 'notificationPreferences');
        }
    }

    /**
     * Init Notification Preferences
     *
     * @return Collection
     */
    public function initNotificationPreferences() {
        $notificationTypeModel = 'Nitm\\Content\\Models\\NotificationType';
        $userModel             = NitmContent::userModel();
        $teamModel             = NitmContent::teamModel();

        $this->load('notificationPreferences');
        $existing = $this->notificationPreferences;
        $types    = class_exists($notificationTypeModel) ? $notificationTypeModel::get() : collect([]);
        if ($types->count() > 0 && $types->count() > $existing->count()) {
            $toAdd  = $types->whereNotIn('id', $existing->pluck('type_id'));
            $teamId = null;
            $userId = null;

            if ($this instanceof $userModel) {
                $userId = $this->id;
            }
            if ($this instanceof $teamModel) {
                $teamId = $this->id;
            }

            $this->setRelation(
                'notificationPreferences',
                $this->notificationPreferences()
                    ->createMany($toAdd->map(function ($type) use ($userId, $teamId) {
                        return [
                            'entity_type' => $userId ? NitmContent::userModel() : NitmContent::teamModel(),
                            'entity_id'   => $userId ?? $teamId,
                            'type_id'     => $type->id,
                            'user_id'     => $userId,
                            'team_id'     => $teamId,
                        ];
                    }))
            );
        }

        return $this->notificationPreferences;
    }
}
