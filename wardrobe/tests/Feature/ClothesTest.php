<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class ClothesTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/clothes/',
            'http_errors' => false
        ]);
    }

    public function test_get_check_clothes_wash_status(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $response = $this->httpClient->get("check_wash/$clothes_id", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertContains($data['data'], [true, false]);
        $this->assertStringContainsString("This clothes is",$data['message']);

        Audit::auditRecordText("Test - Get Check Clothes Wash Status", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Check Clothes Wash Status", "TC-XXX", 'TC-XXX test_get_check_clothes_wash_status', json_encode($data));
    }
}
