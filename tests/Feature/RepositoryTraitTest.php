<?php

namespace Tests\Traits;

use Tests\TestCase;
use Nitm\Content\Models\Post;
use Illuminate\Support\Collection;
use Nitm\Content\Repositories\BaseRepository;

class RepositoryTraitTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize any properties needed for testing
        $this->useUserWithoutTeam();
        $this->repository = new class ($this->app) extends BaseRepository {
            public function model(): string
            {
                return Post::class;
            }
        };
    }

    // public function testToArray()
    // {
    //     // Test the toArray method
    //     $this->assertIsArray($this->repository->toArray());
    // }

    public function testCollectionToArray()
    {
        // Test the collectionToArray method
        $collection = collect([new Post(), new Post()]);
        $this->assertInstanceOf(Collection::class, $this->repository->collectionToArray($collection));
    }

    public function testMakeModel()
    {
        // Test the makeModel method
        $model = $this->repository->makeModel();
        $this->assertInstanceOf(Post::class, $model);
    }

    public function testCreate()
    {
        // Test the create method
        $input = ['title' => 'value'];
        $model = $this->repository->create($input);
        $this->assertNotNull($model);
    }

    public function testFind()
    {
        // Test the find method
        $this->repository->create(['title' => 'value']);
        $model = $this->repository->find(1);
        $this->assertInstanceOf(Post::class, $model);
    }

    public function testUpdate()
    {
        // Test the update method
        $this->repository->create(['title' => 'value']);
        $input = ['title' => 'new value'];
        $model = $this->repository->update($input, 1);
        $this->assertEquals('new value', $model->title);
    }

    public function testDelete()
    {
        // Test the delete method
        $this->repository->create(['title' => 'value']);
        $result = $this->repository->delete(1);
        $this->assertTrue($result);
    }

    // Add more tests for other methods as needed
}