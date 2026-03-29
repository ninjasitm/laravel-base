<?php

use Nitm\Content\Contracts\Repositories\NotificationRepository as NotificationRepositoryContract;
use Nitm\Content\Events\FeedNotificationWasReceived;
use Nitm\Testing\PackageTestCase as TestCase;

class FeedNotificationWasReceivedTest extends TestCase {
    public function testBroadcastWithOnlyReturnsEventData(): void {
        $user      = new stdClass();
        $user->id  = 10;
        $user->foo = 'bar';
        $data      = ['icon' => 'new_releases', 'body' => 'Body'];

        $event = new FeedNotificationWasReceived($data, collect([$user]));

        $this->assertSame($data, $event->broadcastWith());
    }

    public function testPersistManyNotificationsUsesRepositoryBinding(): void {
        $repository = new class implements NotificationRepositoryContract {
            public array $created = [];

            public function recent($user) {
                return collect([]);
            }

            public function create($user, array $data) {
                $this->created[] = ['user' => $user, 'data' => $data];

                return null;
            }

            public function personal($user, $from, array $data) {
                return $this->create($user, array_merge($data, ['from' => $from]));
            }
        };

        $this->app->instance(NotificationRepositoryContract::class, $repository);

        $firstUser      = new stdClass();
        $firstUser->id  = 10;
        $secondUser     = new stdClass();
        $secondUser->id = 11;
        $data           = ['icon' => 'new_releases', 'body' => 'Body'];

        $event = new FeedNotificationWasReceived($data, collect([$firstUser, $secondUser]));
        $event->persistManyNotifications($data);

        $this->assertCount(2, $repository->created);
        $this->assertSame([10, 11], array_map(function ($entry) {
            return $entry['user']->id;
        }, $repository->created));
        $this->assertSame($data, $repository->created[0]['data']);
        $this->assertSame($data, $repository->created[1]['data']);
    }
}