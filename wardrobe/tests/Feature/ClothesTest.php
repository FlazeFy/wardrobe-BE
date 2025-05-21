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
            $check_object = ['id','clothes_name','clothes_note','used_context','created_at'];
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

    public function test_hard_delete_schedule_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "27dbf1e0-a9e5-11ee-aa95-3216422210e8";
        $response = $this->httpClient->delete("destroy_schedule/$clothes_id", [
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
        $this->assertEquals('schedule permanently deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Schedule By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Schedule By ID", "TC-XXX", 'TC-XXX test_hard_delete_schedule_by_id', json_encode($data));
    }

    public function test_recover_clothes_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $clothes_id = "17963858-9771-11ee-8f4a-321642910r4w";
        $response = $this->httpClient->put("recover/$clothes_id", [
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
        $this->assertEquals('clothes recovered',$data['message']);

        Audit::auditRecordText("Test - Recover Clothes By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Recover Clothes By ID", "TC-XXX", 'TC-XXX test_recover_clothes_by_id', json_encode($data));
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
        $this->assertEquals('clothes permanently deleted',$data['message']);

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
        $this->assertEquals('clothes wash permanently deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Wash By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Wash By ID", "TC-XXX", 'TC-XXX test_hard_delete_wash_by_id', json_encode($data));
    }

    public function test_get_deleted_clothes(): void
    {
        // Exec
        $token = $this->login_trait("user");
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

        if(!is_null($data['data']['outfit'])){
            foreach ($data['data']['outfit'] as $dt) {
                $check_object = ['id','outfit_name','outfit_note','is_favorite','created_at','last_used','total_used'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $dt);
                }

                $check_not_null_str = ['id','outfit_name','created_at'];
                foreach ($check_not_null_str as $col) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsString($dt[$col]);
                }

                $check_nullable_str = ['outfit_note','last_used'];
                foreach ($check_nullable_str as $col) {
                    if(!is_null($dt[$col])){
                        $this->assertIsString($dt[$col]);
                    }
                }

                $check_not_null_int = ['is_favorite','total_used'];
                foreach ($check_not_null_int as $col) {
                    $this->assertNotNull($dt[$col]);
                    $this->assertIsInt($dt[$col]);
                    $this->assertGreaterThanOrEqual(0, $dt[$col]);
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
        $this->assertEquals('clothes history created',$data['message']);

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

    public function test_post_generated_outfit(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'clothes_type' => 'hat'
        ];
        $response = $this->httpClient->post("outfit/generate", [
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
        $this->assertArrayHasKey('data', $data);

        foreach ($data['data'] as $dt) {
            $check_object = ['clothes_name','clothes_color','clothes_category','clothes_type','clothes_merk','clothes_image','last_used','total_used'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['clothes_name','clothes_color','clothes_category','clothes_type'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['clothes_merk','clothes_image','last_used'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsString($dt[$col]);
                }
            }

            $check_not_null_str = ['total_used','score'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Post Generated Outfit", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Generated Outfit", "TC-XXX", 'TC-XXX test_post_generated_outfit', json_encode($data));
    }

    public function test_post_save_outfit(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'list_outfit' => [
                [
                    'data' => [
                        [
                            'id' => 'efbf49d9-78f4-436a-07ef-ca3aa661f9d7',
                            'clothes_name' => 'shirt A',
                            'clothes_category' => 'head',
                            'clothes_type' => 'hat',
                            'clothes_merk' => 'Nike',
                            'clothes_made_from' => 'cotton',
                            'clothes_color' => 'blue',
                            'clothes_image' => 'https://storage.googleapis.com',
                            'last_used' => '2025-01-11 11:09:18',
                            'total_used' => 2,
                        ],
                    ],
                    'created_at' => '2025-01-17T03:35:56.340Z',
                    'outfit_name' => 'Outfit Generated 17-Jan-2025 10:35',
                ],
            ],
        ];
        $response = $this->httpClient->post("outfit/save", [
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
        $this->assertStringContainsString('outfit created with',$data['message']);

        Audit::auditRecordText("Test - Post Save Outfit", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Save Outfit", "TC-XXX", 'TC-XXX test_post_save_outfit', json_encode($data));
    }

    public function test_get_all_outfit(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("outfit?page=1", [
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
            $check_object = ['id','outfit_name','outfit_note','is_favorite','total_used','clothes'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','outfit_name'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_not_int = ['is_favorite','total_used'];
            foreach ($check_not_int as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsInt($dt[$col]);
                $this->assertGreaterThanOrEqual(0, $dt[$col]);
            }

            $check_nullable_str = ['outfit_note'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsString($dt[$col]);
                }
            }

            foreach ($dt['clothes'] as $clo) {
                $check_object = ['id','clothes_name','clothes_type','clothes_merk','clothes_color','has_washed','clothes_image'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $clo);
                }

                $check_not_null_str = ['id','clothes_name','clothes_type','clothes_color'];
                foreach ($check_not_null_str as $col) {
                    $this->assertNotNull($clo[$col]);
                    $this->assertIsString($clo[$col]);
                }

                $check_nullable_str = ['clothes_merk','clothes_image'];
                foreach ($check_nullable_str as $col) {
                    if(!is_null($clo[$col])){
                        $this->assertIsString($clo[$col]);
                    }
                }

                $check_not_int = ['has_washed'];
                foreach ($check_not_int as $col) {
                    $this->assertNotNull($clo[$col]);
                    $this->assertIsInt($clo[$col]);
                    $this->assertGreaterThanOrEqual(0, $clo[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get All Outfit", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Outfit", "TC-XXX", 'TC-XXX test_get_all_outfit', json_encode($data));
    }

    public function test_get_last_outfit(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("outfit/last", [
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

        $check_object = ['id','outfit_name','is_favorite','total_used','clothes'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['id','outfit_name'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_not_int = ['is_favorite','total_used'];
        foreach ($check_not_int as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsInt($data['data'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$col]);
        }

        foreach ($data['data']['clothes'] as $clo) {
            $check_object = ['clothes_name','clothes_type','clothes_image'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $clo);
            }

            $check_not_null_str = ['clothes_name','clothes_type'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($clo[$col]);
                $this->assertIsString($clo[$col]);
            }

            $check_nullable_str = ['clothes_image'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($clo[$col])){
                    $this->assertIsString($clo[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get Last Outfit", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Last Outfit", "TC-XXX", 'TC-XXX test_get_last_outfit', json_encode($data));
    }

    public function test_hard_delete_used_outfit_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $outfit_id = "a9649d0e-d633-11ef-96fc-3216422910e8";
        $response = $this->httpClient->delete("outfit/history/by/$outfit_id", [
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
        $this->assertEquals('outfit history permanently deleted',$data['message']);

        Audit::auditRecordText("Test - Hard Delete Used Outfit By ID", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Hard Delete Used Outfit By ID", "TC-XXX", 'TC-XXX test_hard_delete_used_outfit_by_id', json_encode($data));
    }
    
    public function test_get_history_outfit_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $outfit_id = "05d6fe1d-9041-5673-044b-4d2e7f6f0090";
        $response = $this->httpClient->get("outfit/history/$outfit_id", [
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
            $check_object = ['id','created_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['id','created_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }
        }

        Audit::auditRecordText("Test - Get History Outfit By Id", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get History Outfit By Id", "TC-XXX", 'TC-XXX test_get_history_outfit_by_id', json_encode($data));
    }

    public function test_get_outfit_by_id(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $outfit_id = "05d6fe1d-9041-5673-044b-4d2e7f6f0090";
        $response = $this->httpClient->get("outfit/by/$outfit_id", [
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

        $check_object = ['id','outfit_name','is_favorite','total_used','clothes'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['id','outfit_name'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_not_int = ['is_favorite','total_used'];
        foreach ($check_not_int as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsInt($data['data'][$col]);
            $this->assertGreaterThanOrEqual(0, $data['data'][$col]);
        }

        foreach ($data['data']['clothes'] as $clo) {
            $check_object = ['id','clothes_name','clothes_type','clothes_image','is_favorite','has_washed','has_ironed','is_faded','clothes_merk'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $clo);
            }

            $check_not_null_str = ['id','clothes_name','clothes_type'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($clo[$col]);
                $this->assertIsString($clo[$col]);
            }

            $check_nullable_str = ['clothes_image','clothes_merk'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($clo[$col])){
                    $this->assertIsString($clo[$col]);
                }
            }

            $check_not_int = ['is_favorite','has_washed','has_ironed','is_faded'];
                foreach ($check_not_int as $col) {
                    $this->assertNotNull($clo[$col]);
                    $this->assertIsInt($clo[$col]);
                    $this->assertGreaterThanOrEqual(0, $clo[$col]);
                }
        }

        Audit::auditRecordText("Test - Get Last Outfit", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Last Outfit", "TC-XXX", 'TC-XXX test_get_last_outfit', json_encode($data));
    }

    public function test_post_save_outfit_history(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'outfit_id' => '05d6fe1d-9041-5673-044b-4d2e7f6f0090',
            'used_context' => 'Work'
        ];
        $response = $this->httpClient->post("outfit/history/save", [
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
        $this->assertStringContainsString('outfit history created with',$data['message']);

        Audit::auditRecordText("Test - Post Save Outfit History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Save Outfit History", "TC-XXX", 'TC-XXX test_post_save_outfit_history', json_encode($data));
    }

    public function test_get_all_wash_history(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("wash_history", [
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
            $check_object = ['clothes_name','wash_type','clothes_made_from','clothes_color','clothes_type','wash_at','finished_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['clothes_name','wash_type','clothes_made_from','clothes_color','clothes_type','wash_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_str = ['clothes_made_from','finished_at'];
            foreach ($check_nullable_str as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsString($dt[$col]);
                }
            }
        }

        Audit::auditRecordText("Test - Get All Wash History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get All Wash History", "TC-XXX", 'TC-XXX test_get_all_wash_history', json_encode($data));
    }

    public function test_get_unfinished_wash(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("wash_unfinished", [
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
            $check_object = ['clothes_name','wash_type','wash_checkpoint','clothes_type','wash_at'];
            foreach ($check_object as $col) {
                $this->assertArrayHasKey($col, $dt);
            }

            $check_not_null_str = ['clothes_name','wash_type','clothes_type','wash_at'];
            foreach ($check_not_null_str as $col) {
                $this->assertNotNull($dt[$col]);
                $this->assertIsString($dt[$col]);
            }

            $check_nullable_arr = ['wash_checkpoint'];
            foreach ($check_nullable_arr as $col) {
                if(!is_null($dt[$col])){
                    $this->assertIsArray($dt[$col]);
                }
            }

            foreach ($dt['wash_checkpoint'] as $wc) {
                $check_object = ['id','checkpoint_name','is_finished'];
                foreach ($check_object as $col) {
                    $this->assertArrayHasKey($col, $wc);
                }

                $check_not_null_str = ['id','checkpoint_name'];
                foreach ($check_not_null_str as $col) {
                    $this->assertNotNull($wc[$col]);
                    $this->assertIsString($wc[$col]);
                }

                $this->assertIsBool($wc['is_finished']);
            }
        }

        Audit::auditRecordText("Test - Get Unfinished Wash", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Unfinished Wash", "TC-XXX", 'TC-XXX test_get_unfinished_wash', json_encode($data));
    }

    public function test_get_schedule_tomorrow(): void
    {
        // Exec
        $day = "Sat";
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("schedule/tomorrow/$day", [
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

        $check_object = ['tomorrow','tomorrow_day','two_days_later','two_days_later_day'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_not_null_str = ['tomorrow_day','two_days_later_day'];
        foreach ($check_not_null_str as $col) {
            $this->assertNotNull($data['data'][$col]);
            $this->assertIsString($data['data'][$col]);
        }

        $check_nullable_arr = ['tomorrow','two_days_later'];
        foreach ($check_nullable_arr as $col) {
            if(!is_null($data['data'][$col])){
                $this->assertIsArray($data['data'][$col]);

                foreach ($data['data'][$col] as $cl) {
                    $check_object_cl = ['id','clothes_name','clothes_type','clothes_category','clothes_image','day'];
                    foreach ($check_object_cl as $col_cl) {
                        $this->assertArrayHasKey($col_cl, $cl);
                    }

                    $check_not_null_str_cl = ['id','clothes_name','clothes_type','clothes_category','day'];
                    foreach ($check_not_null_str_cl as $col_cl) {
                        $this->assertNotNull($cl[$col_cl]);
                        $this->assertIsString($cl[$col_cl]);
                    }

                    $check_nullable_str_cl = ['clothes_image'];
                    foreach ($check_nullable_str_cl as $col_cl) {
                        if(!is_null($cl[$col_cl])){
                            $this->assertNotNull($cl[$col_cl]);
                            $this->assertIsString($cl[$col_cl]);
                        }
                    }
                }
            }
        }

        Audit::auditRecordText("Test - Get Schedule Tomorrow", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Schedule Tomorrow", "TC-XXX", 'TC-XXX test_get_schedule_tomorrow', json_encode($data));
    }

    public function test_get_last_history(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $response = $this->httpClient->get("last_history", [
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

        $check_object = ['last_added_clothes','last_added_date','last_deleted_clothes','last_deleted_date'];
        foreach ($check_object as $col) {
            $this->assertArrayHasKey($col, $data['data']);
        }

        $check_nullable_str = ['last_added_clothes','last_added_date','last_deleted_clothes','last_deleted_date'];
        foreach ($check_nullable_str as $col) {
            if(!is_null($data['data'][$col])){
                $this->assertNotNull($data['data'][$col]);
                $this->assertIsString($data['data'][$col]);
            }
        }

        Audit::auditRecordText("Test - Get Last History", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Get Last History", "TC-XXX", 'TC-XXX test_get_last_history', json_encode($data));
    }

    public function test_post_wash_clothes(): void
    {
        // Exec
        $token = $this->login_trait("user");
        $body = [
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003',
            'wash_note' => 'test',
            'wash_type' => 'Laundry',
            'wash_checkpoint' => json_encode([
                "id" => 1,
                "checkpoint_name" => "rendam",
                "is_finished" => false
            ]),
        ];
        $response = $this->httpClient->post("wash", [
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
        $this->assertEquals('clothes wash history created',$data['message']);

        Audit::auditRecordText("Test - Post Wash Clothes", "TC-XXX", "Result : ".json_encode($data));
        Audit::auditRecordSheet("Test - Post Wash Clothes", "TC-XXX", 'TC-XXX test_post_wash_clothes', json_encode($data));
    }
}
