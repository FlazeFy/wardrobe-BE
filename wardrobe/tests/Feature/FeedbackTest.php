<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class FeedbackTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/feedback',
            'http_errors' => false
        ]);
    }

    public function test_get_all_feedback(): void
    {
        // Exec
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("", [
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

        foreach ($data['data']['data'] as $dt) {
            $check_object = ['id','feedback_rate','feedback_body','created_at','created_by'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','feedback_body','created_at','created_by'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $this->assertNotNull($dt['feedback_rate']);
            $this->assertIsInt($dt['feedback_rate']);
            $this->assertContains($dt['feedback_rate'], range(1, 5));
        }

        Audit::auditRecordText("Test - Get All Feedback", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Feedback", "TC-XXX", 'TC-XXX test_get_all_feedback', json_encode($data));
    }

    public function test_post_feedback(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            "feedback_rate" => 4,
            "feedback_body" => "testing"
        ];
        $response = $this->httpClient->post("", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ],
            'json' => $body
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('feedback created',$data['message']);

        Audit::auditRecordText("Test - Post Feedback", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Feedback", "TC-XXX", 'TC-XXX test_post_feedback', json_encode($data));
    }
}
