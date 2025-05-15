<?php
namespace App\Helpers;

// Models
use App\Models\ClothesModel;

class Formula
{
    public static function getTemperatureScore($type, $temperature){
        if ($temperature === null) return 0;
        $temp = (int) $temperature;
        $map = [
            'hat' => $temp >= 28 ? 10 : 5,
            'pants' => $temp >= 28 ? 10 : 5,
            'shirt' => ($temp >= 18 && $temp <= 38) ? 10 : 5,
            'jacket' => $temp < 20 ? 10 : 0,
            'shoes' => 5,
            'socks' => $temp < 25 ? 10 : 5,
            'scarf' => $temp < 15 ? 10 : 0,
            'gloves' => $temp < 10 ? 10 : 0,
            'shorts' => $temp >= 28 ? 10 : 0,
            'skirt' => $temp >= 25 ? 10 : 5,
            'dress' => $temp >= 22 ? 10 : 5,
            'blouse' => $temp >= 22 ? 10 : 5,
            'sweater' => $temp < 22 ? 10 : 5,
            'hoodie' => $temp < 22 ? 10 : 5,
            'coat' => $temp < 15 ? 10 : 0,
            'vest' => $temp < 22 ? 5 : 0,
            't-shirt' => $temp >= 20 && $temp <= 30 ? 10 : 5,
            'jeans' => $temp < 34 ? 10 : 5,
            'leggings' => $temp < 25 ? 10 : 5,
            'boots' => $temp < 20 ? 10 : 5,
            'sandals' => $temp >= 28 ? 10 : 5,
            'sneakers' => 5,
            'raincoat' => $temp < 25 ? 10 : 5,
            'poncho' => $temp < 25 ? 10 : 5,
            'cardigan' => $temp < 22 ? 10 : 5,
        ];
        return $map[$type] ?? 0;
    }

    public static function getHumidityScore($type, $humidity){
        if ($humidity === null) return 0;
        $h = (int) $humidity;
        $map = [
            'hat' => $h > 70 ? 10 : 5,
            'pants' => $h < 60 ? 10 : 5,
            'shirt' => $h < 70 ? 10 : 5,
            'jacket' => $h > 80 ? 10 : 5,
            'shoes' => 5,
            'socks' => 5,
            'scarf' => $h < 60 ? 5 : 0,
            'gloves' => $h < 50 ? 5 : 0,
            'shorts' => $h > 75 ? 10 : 5,
            'skirt' => $h > 70 ? 10 : 5,
            'dress' => $h > 70 ? 10 : 5,
            'blouse' => $h > 70 ? 10 : 5,
            'sweater' => $h < 60 ? 10 : 5,
            'hoodie' => $h < 60 ? 10 : 5,
            'coat' => $h > 75 ? 10 : 5,
            'vest' => $h < 60 ? 5 : 0,
            't-shirt' => $h > 70 ? 10 : 5,
            'jeans' => $h < 65 ? 10 : 5,
            'leggings' => $h < 65 ? 10 : 5,
            'boots' => $h > 70 ? 10 : 5,
            'sandals' => $h > 75 ? 10 : 5,
            'sneakers' => 5,
            'raincoat' => $h > 80 ? 10 : 0,
            'poncho' => $h > 80 ? 10 : 0,
            'cardigan' => $h < 65 ? 10 : 5,
        ];
        return $map[$type] ?? 0;
    }

    public static function getWeatherScore($type, $weather){
        if ($weather === null) return 0;
        $weather = strtolower($weather);
        $map = [
            'clouds' => ['hoodie'=>10,'jacket'=>10,'sweater'=>10,'cardigan'=>10,'hat'=>5],
            'rain' => ['raincoat'=>10,'poncho'=>10,'boots'=>10,'hoodie'=>5],
            'clear' => ['t-shirt'=>10,'shorts'=>10,'sandals'=>10,'hat'=>10],
            'snow' => ['coat'=>10,'gloves'=>10,'boots'=>10,'scarf'=>10,'socks'=>10],
            'mist' => ['hoodie'=>10,'jacket'=>10,'hat'=>5],
            'thunderstorm' => ['raincoat'=>10,'poncho'=>10,'boots'=>10]
        ];
        return $map[$weather][$type] ?? 5;
    }

    public static function getColorScore($color, $id) {
        $colors = ClothesModel::getMostUsedColor($id); 
        $color = strtolower($color);
        $total = count($colors);
        $score = 0;
    
        foreach ($colors as $idx => $dt) {
            if (strtolower($dt['context']) === $color) {
                $rank = $idx + 1;
    
                if ($total === 1) {
                    $score = 10;
                } elseif ($total === 2) {
                    $score = $rank === 1 ? 10 : 5;
                } elseif ($total === 3) {
                    $score = match ($rank) { 1 => 10, 2 => 7, 3 => 5, default => 0 };
                } elseif ($total === 4) {
                    $score = match ($rank) { 1 => 10, 2 => 7, 3 => 5, 4 => 3, default => 0};
                } else {
                    $percentile = ($rank - 1) / $total;
    
                    if ($rank === 1) {
                        $score = 10;
                    } elseif ($percentile <= 0.3) {
                        $score = 7;
                    } elseif ($percentile <= 0.6) {
                        $score = 5;
                    } elseif ($percentile <= 0.9) {
                        $score = 3;
                    } else {
                        $score = 0;
                    }
                }
    
                break;
            }
        }
    
        return $score;
    }    
}