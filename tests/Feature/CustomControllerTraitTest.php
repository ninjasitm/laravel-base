<?php

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Nitm\Content\Database\Eloquent\Builder;
use Nitm\Content\Http\Controllers\Traits\CustomControllerTrait;
use Nitm\Content\Models\Category;
use Nitm\Content\Models\User;
use Tests\TestCase;
class CustomControllerTraitTest extends TestCase
{
    use CustomControllerTrait;

    public function testPaginate()
    {
        $request = new Request();
        $query = User::query();

        $result = $this->paginate($request, $query);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testCursorPaginate()
    {
        $request = new Request();
        $query = User::query();

        $result = $this->cursorPaginate($request, $query);

        $this->assertInstanceOf(CursorPaginator::class, $result);
    }

    public function testSimplePaginate()
    {
        $request = new Request();
        $query = User::query();

        $result = $this->simplePaginate($request, $query);

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function testAfterPaginate()
    {
        $request = new Request();
        $query = User::query();

        $result = $this->afterPaginate($request, $query);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testBeforePaginateTransform()
    {
        $request = new Request();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->beforePaginateTransform($request, $paginator);

        $this->assertEquals(0, $paginator->count());
    }

    public function testMakeResponse()
    {
        $data = ['foo' => 'bar'];
        $message = 'Success!';
        $this->setMeta(['test' => true]);

        $result = $this->makeResponse($data, $message);

        $this->assertArrayHasKey('meta', $result);
    }

    public function testSendResponse()
    {
        $result = 'success';
        $message = 'Success!';
        $code = 200;

        $response = $this->sendResponse($result, $message, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testSendError()
    {
        $result = 'error';
        $message = 'Error!';
        $code = 400;

        $response = $this->sendError($result, $message, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testGetMetaInput()
    {
        $key = 'foo';
        $default = 'default';

        $this->assertEquals($default, $this->getMetaInput($key, $default));
    }

    public function testAddMeta()
    {
        $meta = ['foo' => 'bar'];

        $this->addMeta($meta);

        $this->assertArrayHasKey('foo', $this->responseMeta);
    }

    public function testSetMeta()
    {
        $meta = ['foo' => 'bar'];

        $this->setMeta($meta);

        $this->assertEquals($meta, $this->responseMeta);
    }

    public function testAppendMeta()
    {
        $data = ['foo' => 'bar'];
        $this->setMeta(['test' => true]);

        $result = $this->appendMeta($data);

        $this->assertArrayHasKey('meta', $result);
    }

    public function testPrintSuccess()
    {
        $data = ['foo' => 'bar'];
        $status = 'ok';
        $code = 200;

        $response = $this->printSuccess($data, $status, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testPrintModelSuccess()
    {
        $model = new stdClass();
        $status = 'ok';
        $code = 200;

        $response = $this->printModelSuccess($model, $status, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testPrintModelSuccessWithMeta()
    {
        $model = new User();
        $status = 'ok';
        $code = 200;

        $response = $this->printModelSuccessWithMeta($model, $status, $code);

        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testBeforeSendModel()
    {
        $request = new Request();
        $model = new User();

        $this->beforeSendModel($request, $model);

        $this->assertTrue(true);
    }

    public function testPrintSuccessCollection()
    {
        $collection = new Collection();

        $result = $this->printSuccessCollection($collection);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function testExistsOrFail()
    {
        $builder = User::query();
        $model = new user();
        $key = 'id';
        $silently = false;

        $this->expectException(Exception::class);

        $this->existsOrFail($builder, $model, $key, $silently);
    }

    public function testUserOwnsOrFail()
    {
        $user = new User();
        $user->id = 1;
        $model = new Category(['user_id' => -1]);
        $model->id = 2;
        $property = 'user_id';

        $this->expectException(Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->userOwnsOrFail($user, $model, $property);
    }

    public function testUserOwns()
    {
        $user = new User();
        $user->id = 1;
        $model = new Category();
        $model->id = 2;
        $property = null;

        $result = $this->userOwns($user, $model, $property);

        $this->assertFalse($result);
    }
}