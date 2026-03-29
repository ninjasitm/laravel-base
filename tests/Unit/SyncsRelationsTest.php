<?php

use Nitm\Content\Traits\SyncsRelations;
use PHPUnit\Framework\TestCase;

class SyncsRelationsTest extends TestCase {
    public function testSyncHasOneOrManyRelationReturnsEarlyForEmptyCollections(): void {
        $subject = new class {
            use SyncsRelations;

            public int $loadCalls = 0;

            public $items = 'unchanged';

            public function items() {
                throw new RuntimeException('items relation should not be resolved for empty collections');
            }

            public function load($relation) {
                $this->loadCalls++;
            }
        };

        $result = $subject->syncHasOneOrManyRelation(collect([]), 'items');

        $this->assertNull($result);
        $this->assertSame(0, $subject->loadCalls);
    }

    public function testSyncManyToManyRelationUsesSyncWithoutDetaching(): void {
        $relation = new class {
            public array $calls = [];

            public function syncWithoutDetaching($data) {
                $this->calls[] = ['syncWithoutDetaching', $data];

                return $this;
            }

            public function sync($data) {
                $this->calls[] = ['sync', $data];

                return $this;
            }

            public function __call($method, $arguments) {
                $this->calls[] = [$method, $arguments[0] ?? null];

                return $this;
            }
        };

        $subject = new class($relation) {
            use SyncsRelations;

            public int $loadCalls = 0;

            public $tags = null;

            public function __construct(private $relation) {
            }

            public function tags() {
                return $this->relation;
            }

            public function load($relation) {
                $this->loadCalls++;
                $this->$relation = 'loaded';
            }
        };

        $subject->syncManyToManyRelation([1, 2, 3], 'tags', null, false);

        $this->assertSame([
            ['syncWithoutDetaching', [1, 2, 3]],
        ], $relation->calls);
        $this->assertSame(1, $subject->loadCalls);
    }
}