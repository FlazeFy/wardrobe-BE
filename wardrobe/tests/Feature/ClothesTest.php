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

    public function test_get_all_clothes_header(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_category = "head";
        $order = "desc";
        $response = $this->httpClient->get("header/$clothes_category/$order", [
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
            $check_object = ['id','clothes_name','clothes_size','clothes_gender','clothes_color','clothes_category','clothes_type','clothes_qty','is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','clothes_name','clothes_size','clothes_gender','clothes_color','clothes_category','clothes_type'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_not_null_int = ['clothes_qty','is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            $this->assertEquals(36,strlen($dt['id']));
            $check_bool = ['is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
            foreach ($check_bool as $col) {
                $this->assertContains($dt[$col], [0, 1]);
            }
        }

        Audit::auditRecordText("Test - Get All Clothes Header", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Clothes Header", "TC-XXX", 'TC-XXX test_get_all_clothes_header', json_encode($data));
    }

    public function test_get_all_clothes_detail(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_category = "head";
        $order = "desc";
        $response = $this->httpClient->get("detail/$clothes_category/$order", [
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
            $check_object = ['id','clothes_name','clothes_size','clothes_desc','clothes_merk','clothes_gender','clothes_made_from','clothes_color','clothes_category','clothes_type','clothes_qty','clothes_buy_at','is_faded','has_washed','has_ironed','is_favorite','is_scheduled','created_at','updated_at','deleted_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','clothes_name','clothes_size','clothes_gender','clothes_color','clothes_category','clothes_type','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['clothes_desc','clothes_merk','clothes_buy_at','updated_at','deleted_at'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsString($dt[$col]);
                }
            }

            $check_not_null_int = ['clothes_qty','is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
            foreach ($check_not_null_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            if(!is_null($dt['clothes_price'])){
                $this->assertIsInt($dt['clothes_price']);
                $this->assertGreaterThanOrEqual(0, $dt['clothes_price']);
            }

            $this->assertEquals(36,strlen($dt['id']));
            $check_bool = ['is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
            foreach ($check_bool as $col) {
                $this->assertContains($dt[$col], [0, 1]);
            }
        }

        Audit::auditRecordText("Test - Get All Clothes Detail", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Clothes Detail", "TC-XXX", 'TC-XXX test_get_all_clothes_detail', json_encode($data));
    }

    public function test_get_all_clothes_used_history(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $order = "desc";
        $response = $this->httpClient->get("history/$clothes_id/$order", [
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
            $check_object = ['clothes_name','clothes_note','used_context','created_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            if(!is_null($dt['clothes_note'])){
                $this->assertIsString($dt['clothes_note']);
            }
        }

        Audit::auditRecordText("Test - Get All Clothes Used History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Clothes Used History", "TC-XXX", 'TC-XXX test_get_all_clothes_used_history', json_encode($data));
    }
}
