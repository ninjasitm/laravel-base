<?php

namespace Tests;

use Illuminate\Support\Arr;

trait ApiTestTrait
{

    /**
     * @inheritDoc
     */
    public function json($method, $url, array $data = [], array $headers = [])
    {
        $url = ltrim($url, '/');
        $apiUrl = trim($this->getApiBase($url), '/');
        echo "Url: ".strtoupper($method)." /{$apiUrl}\n";
        return parent::json($method, $apiUrl, $data, $headers);
    }

    /**
     * @return [type]
     */
    public function getApiBase($url = '')
    {
        if ($this->usesTeams) {
            $teamId = $this->team ? "{$this->team->id}" : "";
            return "/api/teams/{$teamId}/" . str_replace('/api', '', str_replace('api/', '', $url));
        }
        return str_replace('/api/api', '/api', "/api/{$url}");
    }

    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();

        $response = json_decode($this->response->getContent(), true);
        $responseData = $response['data'];

        $this->assertNotEmpty($responseData['id']);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            $this->assertEquals(Arr::get($actualData, $key, '(not set)'), Arr::get($expectedData, $key, '(not set)'));
        }
    }
}
