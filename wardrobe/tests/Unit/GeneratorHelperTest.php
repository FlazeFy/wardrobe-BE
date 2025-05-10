<?php

namespace Tests\Feature;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Helpers\Generator;

class GeneratorHelperTest extends TestCase
{
    public function test_getUUID()
    {
        // Sample
        $uuid = Generator::getUUID();
        $uuid2 = Generator::getUUID();

        // Test Parameter
        $this->assertIsString($uuid);
        $this->assertEquals(36, strlen($uuid));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',$uuid);
        // Check if UUID is random
        $this->assertNotEquals($uuid, $uuid2);
    }

    public function test_getToken(){
        // Sample
        $token = Generator::getToken();
        $token2 = Generator::getToken();

        // Test Parameter
        $this->assertIsString($token);
        $this->assertEquals(6,strlen($token));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6}$/', $token);
        // Check if Token is random
        $this->assertNotEquals($token,$token2);
    }

    public function test_getRandomDate(){
        // Sample
        $start = '2023-01-01 00:00:00';
        $date = Generator::getRandomDate(0);
        $date2 = Generator::getRandomDate(0);
        $date_null = Generator::getRandomDate(1);

        // Test Parameter
        $this->assertIsString($date);
        $this->assertNull($date_null);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
        $this->assertGreaterThanOrEqual(strtotime($start), strtotime($date));
        // Check if Token is random
        $this->assertNotEquals($date,$date2);
    }

    public function test_getDocTemplate(){
        // Sample
        $type = "footer";
        $html_doc = Generator::getDocTemplate($type);

        // Test Parameter
        // Extract datetime
        preg_match('/Generated at ([0-9]{2} [A-Za-z]{3} [0-9]{4} [0-9]{2}:[0-9]{2})/', $html_doc, $matches);
        $this->assertNotEmpty($matches);
        // Check format
        $datetime = $matches[1];
        $dt = \DateTime::createFromFormat('d M Y H:i', $datetime);
        $this->assertEquals($datetime, $dt->format('d M Y H:i'));
    }
}
