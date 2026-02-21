<?php

namespace Tests\Traits;

use Tests\TestCase;
use Tests\DynamicModel;
use Nitm\Content\Models\Post;
use Nitm\Content\Models\Team;
use Nitm\Content\Models\User;
use Nitm\Content\Traits\Model;

class ModelTraitTest extends TestCase
{
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize any properties needed for testing
        $this->useUserWithoutTeam();
        $this->model = new Post();
        $this->model->fillable = ['published', 'title'];
        $this->model->attributes = [];
        $this->model->id = 1; // Mock ID
        $this->model->exists = true; // Mock existence
    }

    public function testToggle()
    {
        $this->model->published = true;
        $result = $this->model->toggle('published');
        $this->assertFalse($this->model->published);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('published', $result);
    }

    public function testGetStats()
    {
        $team = new Team(); // Mock or create a Team instance
        $stats = ['active_users' => 'active', 'inactive_users' => 'inactive'];
        $result = $this->model->getStats($stats, $team);
        $this->assertArrayHasKey('active_users', $result);
    }

    public function testToRepository()
    {
        $this->expectException(\Exception::class);
        $this->model->toRepository();
    }

    public function testGetFillableForUser()
    {
        $user = new User(); // Mock or create a User instance
        $result = $this->model->getFillableForUser($user);
        $this->assertIsArray($result);
    }

    // public function testHasColumn()
    // {
    //     $this->assertTrue($this->model->hasColumn('published'));
    //     $this->assertFalse($this->model->hasColumn('non_existent_column'));
    // }

    public function testAddFillable()
    {
        $this->model->addFillable('new_field');
        $this->assertContains('new_field', $this->model->fillable);
    }

    public function testAddAppends()
    {
        $this->model->addAppends('new_append');
        $this->assertContains('new_append', $this->model->getAppends());
    }

    public function testAddJsonable()
    {
        $this->model->addJsonable('new_jsonable');
        $this->assertContains('new_jsonable', $this->model->getJsonable());
    }

    // public function testGetTableColumns()
    // {
    //     $columns = $this->model->getTableColumns();
    //     $this->assertIsArray($columns);
    // }

    // public function testGetTableForeignKeys()
    // {
    //     $foreignKeys = $this->model->getTableForeignKeys('some_table');
    //     $this->assertIsArray($foreignKeys);
    // }

    // public function testHasForeignKey()
    // {
    //     $this->assertFalse($this->model->hasForeignKey('some_table', 'non_existent_column'));
    // }

    public function testSetAttributeDirectly()
    {
        $this->model->setAttributeDirectly('published', true);
        $this->assertEquals(true, $this->model->published);
    }

    public function testTitle()
    {
        $this->model->title = 'Test Title';
        $this->assertEquals('Test Title', $this->model->title());
    }

    public function testToArray()
    {
        $this->model->visibleToApi = ['published', 'id'];
        $this->model->attributes = ['published' => true, 'id' => 1, 'name' => 'Test'];
        $result = $this->model->toArray();
        $this->assertArrayHasKey('published', $result['attributes']);
        $this->assertArrayHasKey('id', $result['attributes']);
        $this->assertArrayNotHasKey('name', $result);
    }

    // Additional tests for other methods...
}