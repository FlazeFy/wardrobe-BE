<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class UserTest extends TestCase
{
    protected $httpClient;
    use LoginHelperTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/user/',
            'http_errors' => false
        ]);
    }

    public function test_get_my_profile_admin(): void
    {
        $token = $this->login_trait("admin");
        $response = $this->httpClient->get("my", [
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

        $check_object = ['username','email','created_at','updated_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        if(!is_null($data['data']['updated_at'])){
            $this->assertIsString($data['data']['updated_at']);
        }

        Audit::auditRecordText("Test - Get My Profile Admin", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get My Profile Admin", "TC-XXX", 'TC-XXX test_get_my_profile_admin', json_encode($data));
    }

    public function test_get_my_profile_user(): void
    {
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("my", [
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

        $check_object = ['username','email','created_at','updated_at','telegram_is_valid','telegram_user_id'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_nullable_str = ['updated_at','telegram_user_id'];
        foreach ($check_nullable_str as $col) {
            if(!is_null($data['data'][$col])){
                $this->assertIsString($data['data'][$col]);
            }
        }

        $this->assertIsInt($data['data']['telegram_is_valid']);
        $this->assertContains($data['data']['telegram_is_valid'], [0, 1]);

        Audit::auditRecordText("Test - Get My Profile User", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get My Profile User", "TC-XXX", 'TC-XXX test_get_my_profile_user', json_encode($data));
    }

    public function test_get_my_available_year_filter(): void
    {
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("my_year", [
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
            $this->assertArrayHasKey('year', $dt);
            $this->assertNotNull($dt['year']);
            $this->assertIsInt($dt['year']);
        }

        Audit::auditRecordText("Test - Get My Available Year Filter", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get My Available Year Filter", "TC-XXX", 'TC-XXX test_get_my_available_year_filter', json_encode($data));
    }
}
