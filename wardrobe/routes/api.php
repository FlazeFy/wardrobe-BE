<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\Api\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\Api\ClothesApi\Commands as CommandClothesApi;
use App\Http\Controllers\Api\ClothesApi\Queries as QueriesClothesApi;
use App\Http\Controllers\Api\DictionaryApi\Queries as QueriesDictionaryApi;
use App\Http\Controllers\Api\DictionaryApi\Commands as CommandDictionaryApi;
use App\Http\Controllers\Api\HistoryApi\Queries as QueriesHistoryController;
use App\Http\Controllers\Api\ErrorApi\Queries as QueriesErrorController;
use App\Http\Controllers\Api\StatsApi\Commands as CommandStatsApi;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/clothes')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/header/{category}/{order}', [QueriesClothesApi::class, 'get_all_clothes_header']);
    Route::get('/detail/{category}/{order}', [QueriesClothesApi::class, 'get_all_clothes_detail']);
    Route::get('/history/{clothes_id}/{order}', [QueriesClothesApi::class, 'get_clothes_used_history']);
    Route::get('/similiar/{ctx}/{val}/{exc}', [QueriesClothesApi::class, 'get_clothes_similiar_by']);
    Route::get('/check_wash/{clothes_id}', [QueriesClothesApi::class, 'get_clothes_wash_status_by_clothes_id']);
    Route::get('/wash_checkpoint/{clothes_id}', [QueriesClothesApi::class, 'get_wash_checkpoint_by_clothes_id']);
    Route::get('/trash', [QueriesClothesApi::class, 'get_deleted_clothes']);
    Route::put('/update_checkpoint/{id}', [CommandClothesApi::class, 'update_wash_by_clothes_id']);
    Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hard_delete_clothes_by_id']);
    Route::delete('/destroy_wash/{id}', [CommandClothesApi::class, 'hard_delete_wash_by_id']);
    Route::delete('/delete/{id}', [CommandClothesApi::class, 'soft_delete_clothes_by_id']);
    Route::post('/history', [CommandClothesApi::class, 'post_history_clothes']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/clothes/{ctx}', [CommandStatsApi::class, 'get_stats_clothes_most_context']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'get_all_error']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'get_all_history']);
    Route::delete('/destroy/{id}', [CommandsHistoryController::class, 'hard_delete_history_by_id']);
});

Route::prefix('/v1/dct')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{type}', [QueriesDictionaryApi::class, 'get_dct_by_type']);
    Route::post('/', [CommandDictionaryApi::class, 'post_dct']);
    Route::delete('/{id}', [CommandDictionaryApi::class, 'hard_delete_dct_by_id']);
});