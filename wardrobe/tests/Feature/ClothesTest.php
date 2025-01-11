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

    public function test_get_clothes_similiar_by(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $similiar_val = "Hat";
        $similiar_context = "clothes_name";
        $exclude_clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $response = $this->httpClient->get("similiar/$similiar_context/$similiar_val/$exclude_clothes_id", [
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
            $check_object = ['id','clothes_name','clothes_category','clothes_type'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','clothes_name','clothes_category','clothes_type'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get Clothes Similiar By", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Clothes Similiar By", "TC-XXX", 'TC-XXX test_get_clothes_similiar_by', json_encode($data));
    }

    public function test_get_wash_checkpoint_by_clothes_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $response = $this->httpClient->get("wash_checkpoint/$clothes_id", [
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

        $check_object = ['wash_note','wash_type','wash_checkpoint'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null = ['wash_type','wash_checkpoint'];
        foreach ($check_not_null as $col) {
            $this->assertNotNull($data['data'][$col]);
        }

        if(!is_null($data['data']['wash_checkpoint'])){
            $this->assertIsArray($data['data']['wash_checkpoint']);
            foreach ($data['data']['wash_checkpoint'] as $dt) {
                $check_object = ['id','checkpoint_name','is_finished'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $dt);
                    $this->assertNotNull($dt[$col]);
                }

                $check_not_null_str = ['id','checkpoint_name'];
                foreach ($check_not_null_str as $col) {
                    $this->assertIsString($dt[$col]);
                }

                $this->assertIsBool($dt['is_finished']);
            }
        }

        Audit::auditRecordText("Test - Get Clothes Wash Checkpoint By Clothes Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Clothes Wash Checkpoint By Clothes Id", "TC-XXX", 'TC-XXX test_get_wash_checkpoint_by_clothes_id', json_encode($data));
    }

    public function test_soft_delete_clothes_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $response = $this->httpClient->delete("delete/$clothes_id", [
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
        $this->assertEquals('clothes deleted',$data['message']);

        Audit::auditRecordText("Test - Soft Delete Clothes By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Soft Delete Clothes By ID", "TC-XXX", 'TC-XXX test_soft_delete_clothes_by_id', json_encode($data));
    }

    public function test_hard_delete_clothes_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $response = $this->httpClient->delete("destroy/$clothes_id", [
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
        $this->assertEquals('clothes permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Clothes By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Clothes By ID", "TC-XXX", 'TC-XXX test_hard_delete_clothes_by_id', json_encode($data));
    }

    public function test_hard_delete_wash_clothes_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2599322c-a232-11ee-8c90-0242ac120002";
        $response = $this->httpClient->delete("destroy_wash/$clothes_id", [
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
        $this->assertEquals('clothes wash permentally deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Wash By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Wash By ID", "TC-XXX", 'TC-XXX test_hard_delete_wash_by_id', json_encode($data));
    }

    public function test_get_deleted_clothes(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "2d98f524-de02-11ed-b5ea-0242ac120002";
        $order = "desc";
        $response = $this->httpClient->get("trash", [
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
            $check_object = ['id','clothes_name','clothes_size','clothes_gender','clothes_color','clothes_category','clothes_type','clothes_qty','deleted_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $this->assertNotNull($dt['clothes_qty']);
            $this->assertIsInt($dt['clothes_qty']);
            $this->assertGreaterThanOrEqual(0, $dt['clothes_qty']);
        }

        Audit::auditRecordText("Test - Get Deleted Clothes", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Deleted Clothes", "TC-XXX", 'TC-XXX test_get_deleted_clothes', json_encode($data));
    }

    public function test_get_clothes_detail_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "10bacb64-e819-11ed-a05b-0242ac120003";
        $response = $this->httpClient->get("detail/$clothes_id", [
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

        $check_object = ['id', 'clothes_name', 'clothes_desc', 'clothes_merk', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'clothes_price', 'clothes_buy_at', 'clothes_qty', 'is_faded', 'has_washed', 'has_ironed', 'is_favorite', 'is_scheduled', 'created_at', 'created_by', 'updated_at', 'deleted_at'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']['detail']);
        }

        $check_not_null_str = ['id', 'clothes_name', 'clothes_size', 'clothes_gender', 'clothes_made_from', 'clothes_color', 'clothes_category', 'clothes_type', 'created_at', 'created_by'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data']['detail'][$col]);
            $this->assertIsString($data['data']['detail'][$col]);
        }

        $check_nullable_str = ['clothes_desc','clothes_merk','clothes_buy_at','updated_at','deleted_at'];
        foreach ($check_nullable_str as $col) {
            if(!is_null($data['data']['detail'][$col])){
                $this->assertIsString($data['data']['detail'][$col]);
            }
        }

        $check_not_null_int = ['clothes_qty','is_faded','has_washed','has_ironed','is_favorite','is_scheduled'];
        foreach ($check_not_null_int as $col) {
            $this->assertNotNull($data['data']['detail'][$col]);
            $this->assertIsInt($data['data']['detail'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data']['detail'][$col]);
        }

        if(!is_null($data['data']['used_history'])){
            foreach ($data['data']['used_history'] as $dt) {
                $check_object = ['id', 'used_context', 'clothes_note', 'created_at'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $dt);
                }

                $check_not_null_str = ['id', 'used_context', 'created_at'];
                foreach ($check_not_null_str as $col) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsString($dt[$col]);
                }

                $check_nullable_str = ['clothes_note'];
                foreach ($check_nullable_str as $col) {
                    if(!is_null($dt[$col])){
                        $this->assertIsString($dt[$col]);
                    }
                }
            }
        }

        if(!is_null($data['data']['wash_history'])){
            foreach ($data['data']['wash_history'] as $dt) {
                $check_object = ['wash_type', 'wash_note', 'created_at', 'finished_at','wash_checkpoint'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $dt);
                }

                foreach ($dt['wash_checkpoint'] as $wash_check) {
                    $check_object = ['id', 'checkpoint_name', 'is_finished'];
                    foreach ($check_object as $col) {
                        $this->assertArrayHasKey($col, $wash_check);
                    }

                    $check_not_null_str = ['id', 'checkpoint_name'];
                    foreach ($check_not_null_str as $col) {
                        $this->assertNotNull($wash_check[$col]);
                        $this->assertIsString($wash_check[$col]);
                    }

                    $this->assertIsBool($wash_check['is_finished']);
                }

                $check_nullable_str = ['wash_note','finished_at'];
                foreach ($check_nullable_str as $col) {
                    if(!is_null($dt[$col])){
                        $this->assertIsString($dt[$col]);
                    }
                }
            }
        }

        Audit::auditRecordText("Test - Get Clothes Detail By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Clothes Detail By Id", "TC-XXX", 'TC-XXX test_get_clothes_detail_by_id', json_encode($data));
    }

    public function test_post_history_clothes(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003',
            'clothes_note' => 'test',
            'used_context' => 'Work',
        ];
        $response = $this->httpClient->post("history", [
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
        $this->assertEquals('clothes created',$data['message']);

        Audit::auditRecordText("Test - Post History Clothes", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post History Clothes", "TC-XXX", 'TC-XXX test_post_history_clothes', json_encode($data));
    }

    public function test_post_schedule(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003',
            'day' => 'Sun',
            'schedule_note' => 'test',
            'is_remind' => 1,
        ];
        $response = $this->httpClient->post("schedule", [
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
        $this->assertEquals('schedule created',$data['message']);

        Audit::auditRecordText("Test - Post Schedule", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Schedule", "TC-XXX", 'TC-XXX test_post_schedule', json_encode($data));
    }
}
