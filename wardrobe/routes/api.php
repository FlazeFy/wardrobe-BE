<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\Api\AuthApi\Queries as QueryAuthApi;

use App\Http\Controllers\Api\ClothesApi\Commands as CommandClothesApi;
use App\Http\Controllers\Api\ClothesApi\Queries as QueriesClothesApi;


######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/clothes')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{order}', [QueriesClothesApi::class, 'get_all_clothes_header']);

    Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hard_del_clothes_by_id']);
    Route::delete('/delete/{id}', [CommandClothesApi::class, 'soft_del_clothes_by_id']);
});