<?php

namespace Nitm\Testing;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

trait ApiTestTrait
{
    /**
     * Base API URL
     *
     * @var string
     */
    protected $apiBase = '/api';

    /**
     * Teams base for team Urls
     *
     * @var string
     */
    protected $teamsBase = 'teams';

    /**
     * Whether this test and API uses teams
     *
     * @var bool
     */
    protected $usesTeams = false;

    /**
     * @inheritDoc
     */
    public function json($method, $url, array $data = [], array $headers = [])
    {
        $url = ltrim($url, '/');
        $apiUrl = trim($this->getApiBase($url), '/');
        echo "Url: " . strtoupper($method) . " /{$apiUrl}\n";
        return parent::json($method, $apiUrl, $data, $headers);
    }

    /**
     * Dump the response Json
     *
     * @return void
     */
    public function ddResponseJson()
    {
        dd($this->response->json());
    }

    /**
     * Dump the response
     *
     * @return void
     */
    public function ddResponse()
    {
        dd($this->response);
    }

    /**
     * Dump the response Json
     *
     * @return void
     */
    public function dumpResponseJson()
    {
        dump($this->response->json());
    }

    /**
     * Dump the response
     *
     * @return void
     */
    public function dumpResponse()
    {
        dump($this->response);
    }

    /**
     * Dump the currently defined routes
     *
     * @return void
     */
    public function dumpRoutes()
    {
        $routes = collect(array_map(function (\Illuminate\Routing\Route $route) {
            return [
                "method" => $route->methods[0],
                // 'action' => $route->action,
                'controller' => $route->action['controller'],
                "uri" => $route->uri,
                'as' => Arr::get($route->action, 'as'),
                'middleware' => Arr::get($route->action, 'middleware'),
            ];
        }, (array) Route::getRoutes()->getIterator()))->keyBy('uri');

        dump($routes->toArray());
    }

    /**
     * @return string]
     */
    public function getApiBase($url = '')
    {
        if ($this->usesTeams) {
            $teamId = $this->team ? "{$this->team->id}" : "";
            return "{$this->apiBase}/{$this->teamsBase}/{$teamId}/" . str_replace("{$this->apiBase}", '', str_replace(ltrim($this->apiBase, '/') . '/', '', $url));
        }
        return str_replace("{$this->apiBase}{$this->apiBase}", $this->apiBase, "{$this->apiBase}/{$url}");
    }

    /**
     * Assert Api Response
     *
     * @param  mixed $actualData
     * @return void
     */
    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();

        $response = json_decode($this->response->getContent(), true);
        $responseData = $response['data'];
        $id = Arr::get($responseData, 'id') ?? Arr::get($responseData, 'uuid');
        $this->assertNotEmpty($id);
        $this->assertModelData($actualData, $responseData);
    }

    /**
     * Assert Api Success
     *
     * @return void
     */
    public function assertApiSuccess()
    {
        $this->response->assertJson(['success' => true]);
    }

    /**
     * Assert Api Response/status Code
     *
     * @param  mixed $code
     * @return void
     */
    public function assertApiResponseCode($code = 200)
    {
        $this->response->assertStatus($code);
    }

    /**
     * Assert Api Status Code
     *
     * @param  mixed $code
     * @return void
     */
    public function assertApiStatus($code = 200)
    {
        $this->assertApiResponseCode($code);
    }

    /**
     * assertModelData
     *
     * @param  mixed $actualData
     * @param  mixed $expectedData
     * @return void
     */
    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            $this->assertEquals(Arr::get($actualData, $key, '(not set)'), Arr::get($expectedData, $key, '(not set)'));
        }
    }
}