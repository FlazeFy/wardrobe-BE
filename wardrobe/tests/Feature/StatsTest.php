<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class StatsTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/stats/',
            'http_errors' => false
        ]);
    }

    public function test_get_stats_clothes_most_context(): void
    {
        // Exec
        $ctx = "clothes_merk";

        $token = $this->login_trait("user");
        $response = $this->httpClient->post("clothes/by/$ctx", [
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

        foreach ($data['data'] as $dt) {
            $check_object = ['context','total'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
                $this->assertNotNull($dt[$col]);
            }

            $this->assertIsString($dt['context']);
            $this->assertIsInt($dt['total']);
            $this->assertGreaterThanOrEqual(0, $dt['total']);
        }

        Audit::auditRecordText("Test - Get Stats Clothes Most Context", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Stats Clothes Most Context", "TC-XXX", 'TC-XXX test_get_stats_clothes_most_context', json_encode($data));
    }

    public function test_get_top_feedback(): void
    {
        // Exec
        $response = $this->httpClient->get("feedback/top");

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);

        foreach ($data['data'] as $dt) {
            $check_object = ['feedback_rate','feedback_body','created_at','username'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
                $this->assertNotNull($dt[$col]);
            }

            $check_not_null_str = ['feedback_body','created_at','username'];
            foreach ($check_not_null_str as $col) {
                $this->assertIsString($dt[$col]);
            }

            $this->assertIsInt($dt['feedback_rate']);
        }

        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('average', $data);
        $this->assertIsInt($data['total']);
        $this->assertIsFloat($data['average']);

        Audit::auditRecordText("Test - Get Top Feedback", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Top Feedback", "TC-XXX", 'TC-XXX test_get_top_feedback', json_encode($data));
    }

    public function test_get_all_stats(): void
    {
        // Exec
        $response = $this->httpClient->get("all");

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);

        $check_object = ['total_clothes','total_user','total_schedule','total_outfit_decision'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
            $this->assertIsInt($data['data'][$col]);
        }

        Audit::auditRecordText("Test - Get All Stats", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Stats", "TC-XXX", 'TC-XXX test_get_all_stats', json_encode($data));
    }
}
