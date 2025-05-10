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
        $request = Request::create('/register/token', 'POST', [
            'username' => 'validuser',
            'token' => 'ABC123'
        ]);

        $validator = Validation::getValidateRegisterToken($request);
        $this->assertFalse($validator->fails());
    }
    public function test_validate_register_token_failed_with_invalid_token()
    {
        $request = Request::create('/register/token', 'POST', [
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
        $request = Request::create('/register', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
            'email' => 'user@example.com'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertFalse($validator->fails());
    }
    public function test_validate_register_failed_with_invalid_email()
    {
        $request = Request::create('/register', 'POST', [
            'username' => 'validuser',
            'password' => 'validpass',
            'email' => 'invalid-email'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
    public function test_validate_register_failed_with_long_char_username()
    {
        $request = Request::create('/register', 'POST', [
            'username' => 'validuservaliduservaliduservalid',
            'password' => 'validpass',
            'email' => 'user@gmail.com'
        ]);

        $validator = Validation::getValidateRegister($request);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
    }
}
