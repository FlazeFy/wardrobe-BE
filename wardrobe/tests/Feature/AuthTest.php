<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class AuthTest extends TestCase
{
    protected $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);
    }

    // TC-001
    public function test_post_login()
    {
        // Exec
        $param = [
            'username' => 'flazefy',
            'password' => 'nopass123'
        ];
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => $param
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('role', $data);
        $this->assertArrayHasKey('result', $data);

        $check_object = ['id','username','email','created_at','updated_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['result']);
        }

        $check_not_null_str = ['id','username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($col, $data['result'][$col]);
            $this->assertIsString($col, $data['result'][$col]);
        }
        
        Audit::auditRecordText("Test - Post Login", "TC-001", "Token : ".$data['token']);
        Audit::auditRecordSheet("Test - Post Login", "TC-001", json_encode($param), $data['token']);
        return $data['token'];
    }

    public function test_post_register(): void
    {
        // Exec
        $param = [
            'username' => 'flazefy123',
            'password' => 'nopass123',
            'email' => 'flazen.work@gmail.com'
        ];
        $response = $this->httpClient->post("/api/v1/register", [
            'json' => $param
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals("account has been registered, check your email to get a token validation",$data['message']);
        $this->assertArrayHasKey('result', $data);

        $check_object = ['id','username','email','created_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['result']);
        }

        $check_not_null_str = ['id','username','email','created_at'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($col, $data['result'][$col]);
            $this->assertIsString($col, $data['result'][$col]);
        }
        
        Audit::auditRecordText("Test - Post Register", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Register", "TC-XXX", json_encode($param), json_encode($data));
    }

    public function test_post_validate_register(): void
    {
        // Exec
        $param = [
            'username' => 'flazefy123',
            'token' => 'NGRD0Z',
        ];
        $response = $this->httpClient->post("/api/v1/register/validate", [
            'json' => $param
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("account has been validated. Welcome ".$param['username'], $data['message']);
        
        Audit::auditRecordText("Test - Post Validate Register", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Validate Register", "TC-XXX", json_encode($param), json_encode($data));
    }

    // TC-002
    public function test_get_sign_out(): void
    {
        // Exec
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/logout", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Test Parameter
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('message', $data);

        Audit::auditRecordText("Test - Sign Out", "TC-002", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Sign Out", "TC-002", 'TC-001 test_post_login', json_encode($data));
    }
}
