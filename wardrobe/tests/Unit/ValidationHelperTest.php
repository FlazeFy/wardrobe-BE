<?php

namespace Tests\Feature;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Helpers\Validation;
use Illuminate\Http\Request;

class ValidationHelperTest extends TestCase
{
    // getValidateLogin
    public function test_validate_login_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertFalse($validator->fails());
    }
    public function test_validate_login_failed_with_missing_username()
    {
        $request = Request::create('/test', 'POST', [
            'password' => 'validpass'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
    }
    public function test_validate_login_failed_with_short_password()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => '123'
        ]);

        $validator = Validation::getValidateLogin($request);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    // getValidateRegisterToken
    public function test_validate_register_token_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'token' => 'ABC123'
        ]);

        $validator = Validation::getValidateRegisterToken($request);
        $this->assertFalse($validator->fails());
    }
    public function test_validate_register_token_failed_with_invalid_token()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'token' => '123' 
        ]);

        $validator = Validation::getValidateRegisterToken($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }

    // getValidateRegister
    public function test_validate_register_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
            'email' => 'user@example.com'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertFalse($validator->fails());
    }
    public function test_validate_register_failed_with_invalid_email()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
            'email' => 'invalid-email'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
    public function test_validate_register_failed_with_invalid_long_char_username()
    {
        $request = Request::create('/test', 'POST', [
            'username' => 'validuservaliduservaliduservalid',
            'password' => 'validpass',
            'email' => 'user@gmail.com'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
    }

    // getValidateUser
    public function test_validate_user_update_fcm_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'firebase_fcm_token' => 'a90su1a9d09109u3',
        ]);

        $validator = Validation::getValidateUser($request,"update_fcm");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_user_update_fcm_failed_with_invalid_long_char_fcm()
    {
        $request = Request::create('/test', 'POST', [
            'firebase_fcm_token' => 'a90',
        ]);

        $validator = Validation::getValidateUser($request,"update_fcm");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('firebase_fcm_token', $validator->errors()->toArray());
    }

    // getValidateDictionary
    public function test_validate_dictionary_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'wash_type',
        ]);

        $validator = Validation::getValidateDictionary($request,"create");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_dictionary_create_failed_with_invalid_long_char_dictionary_name()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 't',
            'dictionary_type' => 'wash_type',
        ]);

        $validator = Validation::getValidateDictionary($request,"create");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_name', $validator->errors()->toArray());
    }
    public function test_validate_dictionary_create_failed_with_invalid_rules_dictionary_type()
    {
        $request = Request::create('/test', 'POST', [
            'dictionary_name' => 'test',
            'dictionary_type' => 'wash_note',
        ]);

        $validator = Validation::getValidateDictionary($request,"create");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dictionary_type', $validator->errors()->toArray());
    }
    public function test_validate_dictionary_delete_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'id' => 'a7Fq2XpR9vLEcYz81NMKoh6dsWJpUtgBXie3',
        ]);

        $validator = Validation::getValidateDictionary($request,"delete");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_dictionary_delete_failed_with_invalid_long_char_id()
    {
        $request = Request::create('/test', 'POST', [
            'id' => 'a7Fq2XpR9vLEcYz81NMKoh6dsWJpUtgBXie34A',
        ]);

        $validator = Validation::getValidateDictionary($request,"delete");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    // getValidateQuestion
    public function test_validate_question_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'question' => 'test',
        ]);

        $validator = Validation::getValidateQuestion($request,"create");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_question_create_failed_with_invalid_long_char_question()
    {
        $request = Request::create('/test', 'POST', [
            'question' => '1',
        ]);

        $validator = Validation::getValidateQuestion($request,"create");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    // getValidateClothes
    public function test_validate_clothes_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_desc' => 'Casual cotton shirt',
            'clothes_merk' => 'Merk A',
            'clothes_size' => 'M',
            'clothes_gender' => 'male',
            'clothes_made_from' => 'cotton',
            'clothes_color' => 'black',
            'clothes_category' => 'upper_body',
            'clothes_type' => 'shirt',
            'clothes_price' => 199000,
            'clothes_buy_at' => '2024-01-01',
            'clothes_qty' => 3,
            'is_faded' => false,
            'has_washed' => true,
            'has_ironed' => true,
            'is_favorite' => false,
        ]);

        $validator = Validation::getValidateClothes($request, 'create');
        $this->assertFalse($validator->fails());
    }
    public function test_validate_clothes_create_failed_with_long_char_clothes_price()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_desc' => 'Casual cotton shirt',
            'clothes_merk' => 'Merk A',
            'clothes_size' => 'M',
            'clothes_gender' => 'male',
            'clothes_made_from' => 'cotton',
            'clothes_color' => 'black',
            'clothes_category' => 'upper_body',
            'clothes_type' => 'shirt',
            'clothes_price' => 9999999999,
            'clothes_buy_at' => '2024-01-01',
            'clothes_qty' => 3,
            'is_faded' => false,
            'has_washed' => true,
            'has_ironed' => true,
            'is_favorite' => false,
        ]);

        $validator = Validation::getValidateClothes($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_price', $validator->errors()->toArray());
    }
    public function test_validate_clothes_create_failed_with_invalid_rules_clothes_size()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_desc' => 'Casual cotton shirt',
            'clothes_merk' => 'Merk A',
            'clothes_size' => 'ML',
            'clothes_gender' => 'male',
            'clothes_made_from' => 'cotton',
            'clothes_color' => 'black',
            'clothes_category' => 'upper_body',
            'clothes_type' => 'shirt',
            'clothes_price' => 199000,
            'clothes_buy_at' => '2024-01-01',
            'clothes_qty' => 3,
            'is_faded' => false,
            'has_washed' => true,
            'has_ironed' => true,
            'is_favorite' => false,
        ]);

        $validator = Validation::getValidateClothes($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_size', $validator->errors()->toArray());
    }
    public function test_validate_clothes_create_failed_with_invalid_clothes_buy_at()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_desc' => 'Casual cotton shirt',
            'clothes_merk' => 'Merk A',
            'clothes_size' => 'M',
            'clothes_gender' => 'male',
            'clothes_made_from' => 'cotton',
            'clothes_color' => 'black',
            'clothes_category' => 'upper_body',
            'clothes_type' => 'shirt',
            'clothes_price' => 199000,
            'clothes_buy_at' => '2024-01',
            'clothes_qty' => 3,
            'is_faded' => false,
            'has_washed' => true,
            'has_ironed' => true,
            'is_favorite' => false,
        ]);

        $validator = Validation::getValidateClothes($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_buy_at', $validator->errors()->toArray());
    }
    public function test_validate_clothes_create_outfit_relation_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_type' => 'shirt',
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003',
        ]);

        $validator = Validation::getValidateClothes($request, 'create_outfit_relation');
        $this->assertFalse($validator->fails());
    }
    public function test_validate_clothes_create_outfit_relation_failed_with_invalid_rules_clothes_type()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_type' => 't-pants',
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003',
        ]);

        $validator = Validation::getValidateClothes($request, 'create_outfit_relation');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_type', $validator->errors()->toArray());
    }
    public function test_validate_clothes_create_outfit_relation_failed_with_invalid_clothes_id()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_name' => 'T-Shirt',
            'clothes_type' => 'shirt',
            'clothes_id' => '10bacb64-e819-11ed-a05b-0242ac120003sa',
        ]);

        $validator = Validation::getValidateClothes($request, 'create_outfit_relation');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_id', $validator->errors()->toArray());
    }

    // getValidateWash
    public function test_validate_wash_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'wash_note' => 'Weekly wash',
            'clothes_id' => '123e4567-e89b-12d3-a456-426614174000',
            'wash_type' => 'Laundry',
            'wash_checkpoint' => [
                [
                    'id' => 1,
                    'checkpoint_name' => 'Sorting',
                    'is_finished' => true
                ],
                [
                    'id' => 2,
                    'checkpoint_name' => 'Drying',
                    'is_finished' => false
                ]
            ]
        ]);

        $validator = Validation::getValidateWash($request, 'create');
        $this->assertFalse($validator->fails());
    }
    public function test_validate_wash_create_failed_with_long_char_wash_note()
    {
        $request = Request::create('/test', 'POST', [
            'wash_note' => 'A',
            'clothes_id' => '123e4567-e89b-12d3-a456-426614174000',
            'wash_type' => 'Laundry',
            'wash_checkpoint' => [
                [
                    'id' => 1,
                    'checkpoint_name' => 'Sorting',
                    'is_finished' => true
                ],
                [
                    'id' => 2,
                    'checkpoint_name' => 'Drying',
                    'is_finished' => false
                ]
            ]
        ]);

        $validator = Validation::getValidateWash($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('wash_note', $validator->errors()->toArray());
    }
    public function test_validate_wash_create_failed_with_invalid_wash_type()
    {
        $request = Request::create('/test', 'POST', [
            'wash_note' => 'Weekly wash',
            'clothes_id' => '123e4567-e89b-12d3-a456-426614174000',
            'wash_type' => 'Clothes',
            'wash_checkpoint' => [
                [
                    'id' => 1,
                    'checkpoint_name' => 'Sorting',
                    'is_finished' => true
                ],
                [
                    'id' => 2,
                    'checkpoint_name' => 'Drying',
                    'is_finished' => false
                ]
            ]
        ]);

        $validator = Validation::getValidateWash($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('wash_type', $validator->errors()->toArray());
    }
    public function test_validate_wash_create_failed_with_invalid_wash_checkpoint_structure()
    {
        $request = Request::create('/test', 'POST', [
            'wash_note' => 'Weekly wash',
            'clothes_id' => '123e4567-e89b-12d3-a456-426614174000',
            'wash_type' => 'Laundry',
            'wash_checkpoint' => [
                [
                    'id' => 1,
                    'checkpoint_name' => null,
                    'is_finished' => true
                ]
            ]
        ]);

        $validator = Validation::getValidateWash($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('wash_checkpoint.0.checkpoint_name', $validator->errors()->toArray());
    }

    // getValidateClothesUsed
    public function test_validate_clothes_used_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_note' => 'test note',
            'used_context' => 'Work',
        ]);

        $validator = Validation::getValidateClothesUsed($request, 'create');
        $this->assertFalse($validator->fails());
    }
    public function test_validate_clothes_used_create_failed_with_long_clothes_note()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_note' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat',
            'used_context' => 'test note',
        ]);

        $validator = Validation::getValidateClothesUsed($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('clothes_note', $validator->errors()->toArray());
    }
    public function test_validate_clothes_used_create_failed_with_invalid_used_context()
    {
        $request = Request::create('/test', 'POST', [
            'clothes_note' => 'test note',
            'used_context' => 'test',
        ]);

        $validator = Validation::getValidateClothesUsed($request, 'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('used_context', $validator->errors()->toArray());
    }

    // getValidateSchedule
    public function test_validate_schedule_create_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'is_remind' => 1,
            'schedule_note' => 'test',
            'day' => 'Sun'
        ]);

        $validator = Validation::getValidateSchedule($request,'create');
        $this->assertFalse($validator->fails());
    }
    public function test_validate_schedule_create_failed_with_invalid_is_remind()
    {
        $request = Request::create('/test', 'POST', [
            'is_remind' => 2,
            'schedule_note' => 'test',
            'day' => 'Sun'
        ]);

        $validator = Validation::getValidateSchedule($request,'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('is_remind', $validator->errors()->toArray());
    }
    public function test_validate_schedule_create_failed_with_missing_day()
    {
        $request = Request::create('/test', 'POST', [
            'is_remind' => 1,
            'schedule_note' => 'test'
        ]);

        $validator = Validation::getValidateSchedule($request,'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('day', $validator->errors()->toArray());
    }
    public function test_validate_schedule_create_failed_with_invalid_rules_day()
    {
        $request = Request::create('/test', 'POST', [
            'is_remind' => 1,
            'schedule_note' => 'test',
            'day' => 'Sunday'
        ]);

        $validator = Validation::getValidateSchedule($request,'create');
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('day', $validator->errors()->toArray());
    }

    // getValidateFeedback
    public function test_validate_feedback_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'feedback_rate' => 4,
            'feedback_body' => 'test',
        ]);

        $validator = Validation::getValidateFeedback($request);
        $this->assertFalse($validator->fails());
    }
    public function test_validate_feedback_failed_with_invalid_feedback_rate()
    {
        $request = Request::create('/test', 'POST', [
            'feedback_rate' => 6,
            'feedback_body' => 'test',
        ]);

        $validator = Validation::getValidateFeedback($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('feedback_rate', $validator->errors()->toArray());
    }
    public function test_validate_feedback_failed_with_missing_feedback_body()
    {
        $request = Request::create('/test', 'POST', [
            'feedback_rate' => 4,
        ]);

        $validator = Validation::getValidateFeedback($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('feedback_body', $validator->errors()->toArray());
    }

    // getValidateStats
    public function test_validate_stats_most_context_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'context' => 'clothes_merk',
        ]);

        $validator = Validation::getValidateStats($request,"most_context");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_stats_most_context_failed_with_invalid_rules_context()
    {
        $request = Request::create('/test', 'POST', [
            'context' => 'clothes_name',
        ]);

        $validator = Validation::getValidateStats($request,"most_context");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('context', $validator->errors()->toArray());
    }
    public function test_validate_stats_yearly_context_success_with_valid_data()
    {
        $request = Request::create('/test', 'POST', [
            'context' => 'clothes_created_at',
        ]);

        $validator = Validation::getValidateStats($request,"yearly_context");
        $this->assertFalse($validator->fails());
    }
    public function test_validate_stats_yearly_context_failed_with_invalid_rules_context()
    {
        $request = Request::create('/test', 'POST', [
            'context' => 'clothes_created_by',
        ]);

        $validator = Validation::getValidateStats($request,"yearly_context");
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('context', $validator->errors()->toArray());
    }

    // hasNumber
    public function test_validate_has_number_with_valid_data()
    {
        $validator = Validation::hasNumber("as241ad");
        $this->assertEquals($validator,true);
    }
    public function test_validate_has_number_with_invalid_data()
    {
        $validator = Validation::hasNumber("asad");
        $this->assertEquals($validator,false);
    }
}
