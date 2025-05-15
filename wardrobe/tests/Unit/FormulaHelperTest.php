<?php

namespace Tests\Feature;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Helpers\Formula;
use Mockery;

class FormulaHelperTest extends TestCase
{
    // getTemperatureScore
    public function test_temperature_score_valid_item()
    {
        $score = Formula::getTemperatureScore('jacket', 15);
        $this->assertEquals(10, $score);
    }
    public function test_temperature_score_invalid_item()
    {
        $score = Formula::getTemperatureScore('unknown_item', 15);
        $this->assertEquals(0, $score);
    }
    public function test_temperature_score_null_temperature()
    {
        $score = Formula::getTemperatureScore('jacket', null);
        $this->assertEquals(0, $score);
    }

    // getHumidityScore
    public function test_humidity_score_valid_item()
    {
        $score = Formula::getHumidityScore('coat', 80);
        $this->assertEquals(10, $score); 
    }
    public function test_humidity_score_invalid_item()
    {
        $score = Formula::getHumidityScore('invalid_type', 80);
        $this->assertEquals(0, $score);
    }
    public function test_humidity_score_null_humidity()
    {
        $score = Formula::getHumidityScore('coat', null);
        $this->assertEquals(0, $score);
    }

    // getWeatherScore
    public function test_weather_score_valid_item()
    {
        $score = Formula::getWeatherScore('raincoat', 'Rain');
        $this->assertEquals(10, $score);
    }
    public function test_weather_score_not_in_weather_map()
    {
        $score = Formula::getWeatherScore('random_item', 'Rain');
        $this->assertEquals(5, $score); 
    }
    public function test_weather_score_invalid_weather_type()
    {
        $score = Formula::getWeatherScore('coat', 'Fog');
        $this->assertEquals(5, $score); 
    }
    public function test_weather_score_null_weather()
    {
        $score = Formula::getWeatherScore('raincoat', null);
        $this->assertEquals(0, $score);
    }

    // getColorScore
    public function test_color_score_valid_high_rank()
    {
        $mock = Mockery::mock('alias:App\\Models\\ClothesModel');
        $mock->shouldReceive('getMostUsedColor')
            ->andReturn([
                ['context' => 'black'],
                ['context' => 'white'],
                ['context' => 'blue'],
            ]);

        $score = Formula::getColorScore('black', null);
        $this->assertEquals(10, $score); 
    }
    public function test_color_score_valid_low_rank()
    {
        $mock = Mockery::mock('alias:App\\Models\\ClothesModel');
        $mock->shouldReceive('getMostUsedColor')
            ->andReturn([
                ['context' => 'black'],
                ['context' => 'white'],
                ['context' => 'blue'],
                ['context' => 'red'],
            ]);

        $score = Formula::getColorScore('white', null);
        $this->assertEquals(7, $score);
    }
    public function test_color_score_color_not_found()
    {
        $mock = Mockery::mock('alias:App\\Models\\ClothesModel');
        $mock->shouldReceive('getMostUsedColor')
            ->andReturn([
                ['context' => 'black'],
                ['context' => 'white'],
            ]);

        $score = Formula::getColorScore('green', null);
        $this->assertEquals(0, $score);
    }
}
