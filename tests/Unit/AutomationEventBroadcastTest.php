<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nitm\Content\Events\NewComment;
use Nitm\Content\Events\NewReaction;
use Nitm\Content\Models\NotificationPreference;
use Nitm\Content\Models\NotificationType;
use Nitm\Content\Models\User;
use Tests\TestCase;

class AutomationEventBroadcastTest extends TestCase {
    public function testNewCommentBroadcastOnBuildsPrivateChannelsForDistinctRecipients(): void {
        $this->prepareNotificationEnvironment();

        $actor           = User::factory()->create(['email' => 'comment-actor@app.local']);
        $firstRecipient  = User::factory()->create(['email' => 'comment-first@app.local']);
        $secondRecipient = User::factory()->create(['email' => 'comment-second@app.local']);

        $this->createWebPreference(NewComment::class, $firstRecipient->id);
        $this->createWebPreference(NewComment::class, $secondRecipient->id);

        $commentable = new class([$actor->id, $firstRecipient->id, $secondRecipient->id, $secondRecipient->id]) {
            public function __construct(private array $userIds) {
            }

            public function comments() {
                return collect(array_map(function ($userId) {
                    return ['user_id' => $userId];
                }, $this->userIds));
            }
        };

        $comment = new class extends Model {
            protected $guarded = [];
        };
        $comment->user_id     = $actor->id;
        $comment->commentable = $commentable;

        $channels = (new NewComment($comment))->broadcastOn();

        $this->assertSame(
            [
                'private-users.' . $firstRecipient->id,
                'private-users.' . $secondRecipient->id,
            ],
            array_map(function ($channel) {
                return $channel->name;
            }, $channels)
        );
    }

    public function testNewReactionBroadcastOnBuildsPrivateChannelsFromReactableParticipants(): void {
        $this->prepareNotificationEnvironment();

        $actor     = User::factory()->create(['email' => 'reaction-actor@app.local']);
        $owner     = User::factory()->create(['email' => 'reaction-owner@app.local']);
        $commenter = User::factory()->create(['email' => 'reaction-commenter@app.local']);

        $this->createWebPreference(NewReaction::class, $owner->id);
        $this->createWebPreference(NewReaction::class, $commenter->id);

        $reactable = new class($owner->id, [$owner->id, $commenter->id, $actor->id]) {
            public function __construct(public int $user_id, private array $userIds) {
            }

            public function comments() {
                return collect(array_map(function ($userId) {
                    return ['user_id' => $userId];
                }, $this->userIds));
            }
        };

        $reaction = new class extends Model {
            protected $guarded = [];
        };
        $reaction->user_id  = $actor->id;
        $reaction->reactant = new class($reactable) {
            public function __construct(public $reactable) {
            }
        };

        $channels = (new NewReaction($reaction))->broadcastOn();

        $this->assertSame(
            [
                'private-users.' . $owner->id,
                'private-users.' . $commenter->id,
            ],
            array_map(function ($channel) {
                return $channel->name;
            }, $channels)
        );
    }

    private function prepareNotificationEnvironment(): void {
        $this->ensureNotificationTables();

        User::resolveRelationUsing('notificationPreferences', function ($user) {
            return $user->hasMany(NotificationPreference::class, 'user_id', 'id');
        });
    }

    private function ensureNotificationTables(): void {
        if (! Schema::hasTable('notification_types')) {
            Schema::create('notification_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('notification_class');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->unsignedInteger('team_id')->nullable();
                $table->unsignedInteger('type_id');
                $table->boolean('via_web')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }

        NotificationPreference::query()->delete();
        NotificationType::query()->delete();
    }

    private function createWebPreference(string $notificationClass, int $userId): void {
        $type = NotificationType::query()->create([
            'notification_class' => $notificationClass,
        ]);

        NotificationPreference::query()->create([
            'user_id'    => $userId,
            'team_id'    => null,
            'type_id'    => $type->id,
            'via_web'    => true,
            'is_enabled' => true,
        ]);
    }
}