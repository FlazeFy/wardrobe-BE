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
    private $month;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/stats/',
            'http_errors' => false
        ]);
        $this->month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
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

    public function test_get_stats_clothes_monthly_created_buyed(): void
    {
        // Exec
        $year = "2025";

        $token = $this->login_trait("user");
        $response = $this->httpClient->get("clothes/monthly/created_buyed/$year", [
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
            $check_object = ['context','total_created','total_buyed'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
                $this->assertNotNull($dt[$col]);
            }

            $this->assertIsString($dt['context']);

            $this->assertTrue(in_array($dt['context'], $this->month));

            $check_not_null_int = ['total_created','total_buyed'];
            foreach ($check_not_null_int as $col) {
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Clothes Monthly Created Buyed", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Clothes Monthly Created Buyed", "TC-XXX", 'TC-XXX test_get_stats_clothes_monthly_created_buyed', json_encode($data));
    }

    public function test_get_stats_outfit_monthly_by_outfit_id(): void
    {
        // Exec
        $year = "2025";

        $token = $this->login_trait("user");
        $response = $this->httpClient->get("outfit/monthly/by_outfit/$year/all", [
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

            $this->assertTrue(in_array($dt['context'], $this->month));

            $check_not_null_int = ['total'];
            foreach ($check_not_null_int as $col) {
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Stats Outfit Monthly By Outfit Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Stats Outfit Monthly By Outfit Id", "TC-XXX", 'TC-XXX test_get_stats_outfit_monthly_by_outfit_id', json_encode($data));
    }

    public function test_get_stats_outfit_yearly_most_used(): void
    {
        // Exec
        $year = "2025";

        $token = $this->login_trait("user");
        $response = $this->httpClient->get("outfit/most/used/$year", [
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

            $check_not_null_int = ['total'];
            foreach ($check_not_null_int as $col) {
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Stats Outfit Yearly Most Used", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Stats Outfit Yearly Most Used", "TC-XXX", 'TC-XXX test_get_stats_outfit_yearly_most_used', json_encode($data));
    }

    public function test_get_stats_wash_summary(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("wash/summary", [
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

        $check_object = ['last_wash_clothes','last_wash_date','most_wash','total_wash','avg_wash_dur_per_clothes','avg_wash_per_week'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
            $this->assertNotNull($data['data'][$col]);
        }

        $check_not_null_str = ['last_wash_clothes','last_wash_date','most_wash'];
        foreach ($check_not_null_str as $col) {
            $this->assertIsString($data['data'][$col]);
        }

        $check_not_null_int = ['total_wash','avg_wash_dur_per_clothes','avg_wash_per_week'];
        foreach ($check_not_null_int as $col) {
            $this->assertIsInt($data['data'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$col]);
        }

        Audit::auditRecordText("Test - Get Stats Wash Summary", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Stats Wash Summary", "TC-XXX", 'TC-XXX test_get_stats_wash_summary', json_encode($data));
    }
}
