<?php
namespace Nitm\Content\Models;

use Nitm\Content\Models\BaseModel as Model;
use Nitm\Content\Traits\SupportsNotificationPreferences;

class NotificationPreference extends Model {
    use SupportsNotificationPreferences;

    public const VIA_WEB = 'web';

    protected $table = 'notification_preferences';

    protected $guarded = [];

    public function type() {
        return $this->belongsTo(\Nitm\Content\Models\NotificationType::class, 'type_id');
    }
}