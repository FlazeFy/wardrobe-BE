<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
use App\Http\Controllers\Api\AuthApi\Queries as QueryAuthApi;
use App\Http\Controllers\Api\ClothesApi\Commands as CommandClothesApi;
use App\Http\Controllers\Api\ClothesApi\Queries as QueriesClothesApi;
use App\Http\Controllers\Api\FeedbackApi\Commands as CommandFeedbackApi;
use App\Http\Controllers\Api\FeedbackApi\Queries as QueriesFeedbackApi;
use App\Http\Controllers\Api\DictionaryApi\Queries as QueriesDictionaryApi;
use App\Http\Controllers\Api\DictionaryApi\Commands as CommandDictionaryApi;
use App\Http\Controllers\Api\HistoryApi\Queries as QueriesHistoryApi;
use App\Http\Controllers\Api\HistoryApi\Commands as CommandHistoryApi;
use App\Http\Controllers\Api\ErrorApi\Queries as QueriesErrorController;
use App\Http\Controllers\Api\StatsApi\Commands as CommandStatsApi;
use App\Http\Controllers\Api\StatsApi\Queries as QueriesStatsApi;
use App\Http\Controllers\Api\UserApi\Queries as QueriesUserApi;
use App\Http\Controllers\Api\UserApi\Commands as CommandUserApi;
use App\Http\Controllers\Api\ChatApi\Commands as CommandChatApi;
use App\Http\Controllers\Api\ExportApi\Queries as QueriesExportApi;
use App\Http\Controllers\Api\QuestionApi\Queries as QueriesQuestionApi;
use App\Http\Controllers\Api\QuestionApi\Commands as CommandQuestionApi;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthApi::class, 'login']);
Route::prefix('/v1/register')->group(function () {
    Route::post('/', [CommandAuthApi::class, 'register']);
    Route::post('/validate', [CommandAuthApi::class, 'validate_register']);
});

Route::prefix('/v1/question')->group(function () {
    Route::get('/faq', [QueriesQuestionApi::class, 'get_question_faq']);
    Route::post('/', [CommandQuestionApi::class, 'post_question']);
});

Route::prefix('/v1/stats')->group(function () {
    Route::get('/all', [QueriesStatsApi::class, 'get_all_stats']);
    Route::get('/feedback/top', [QueriesStatsApi::class, 'get_top_feedback']);
});

######################### Private Route #########################

Route::get('/v1/logout', [QueryAuthApi::class, 'logout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/clothes')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/header/{category}/{order}', [QueriesClothesApi::class, 'get_all_clothes_header']);
    Route::get('/detail/{category}/{order}', [QueriesClothesApi::class, 'get_all_clothes_detail']);
    Route::get('/detail/{clothes_id}', [QueriesClothesApi::class, 'get_clothes_detail_by_id']);
    Route::get('/history/{clothes_id}/{order}', [QueriesClothesApi::class, 'get_clothes_used_history']);
    Route::get('/similiar/{ctx}/{val}/{exc}', [QueriesClothesApi::class, 'get_clothes_similiar_by']);
    Route::get('/check_wash/{clothes_id}', [QueriesClothesApi::class, 'get_clothes_wash_status_by_clothes_id']);
    Route::get('/wash_checkpoint/{clothes_id}', [QueriesClothesApi::class, 'get_wash_checkpoint_by_clothes_id']);
    Route::get('/wash_history', [QueriesClothesApi::class, 'get_all_wash_history']);
    Route::get('/wash_unfinished', [QueriesClothesApi::class, 'get_unfinished_wash']);
    Route::get('/trash', [QueriesClothesApi::class, 'get_deleted_clothes']);
    Route::get('/schedule/{day}', [QueriesClothesApi::class, 'get_schedule_by_day']);
    Route::get('/schedule/tomorrow/{day}', [QueriesClothesApi::class, 'get_schedule_tomorrow']);
    Route::get('/last_history', [QueriesClothesApi::class, 'get_last_history']);
    Route::put('/update_checkpoint/{id}', [CommandClothesApi::class, 'update_wash_by_clothes_id']);
    Route::put('/recover/{id}', [CommandClothesApi::class, 'recover_clothes_by_id']);
    Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hard_delete_clothes_by_id']);
    Route::delete('/destroy_wash/{id}', [CommandClothesApi::class, 'hard_delete_wash_by_id']);
    Route::delete('/delete/{id}', [CommandClothesApi::class, 'soft_delete_clothes_by_id']);
    Route::delete('/outfit/remove/{clothes_id}/{outfit_id}', [CommandClothesApi::class, 'hard_delete_clothes_outfit_by_id']);
    Route::delete('/destroy_used/{id}', [CommandClothesApi::class, 'hard_delete_clothes_used_by_id']);
    Route::delete('/destroy_schedule/{id}', [CommandClothesApi::class, 'hard_delete_schedule_by_id']);
    Route::post('/history', [CommandClothesApi::class, 'post_history_clothes']);
    Route::post('/', [CommandClothesApi::class, 'post_clothes']);
    Route::post('/wash', [CommandClothesApi::class, 'post_wash_clothes']);
    Route::post('/schedule', [CommandClothesApi::class, 'post_schedule']);
    Route::post('/outfit/generate', [CommandClothesApi::class, 'post_generated_outfit']);
    Route::post('/outfit/save', [CommandClothesApi::class, 'post_save_outfit']);
    Route::post('/outfit/save/clothes', [CommandClothesApi::class, 'post_save_clothes_outfit']);
    Route::post('/outfit/history/save', [CommandClothesApi::class, 'post_save_outfit_history']);
    Route::get('/outfit', [QueriesClothesApi::class, 'get_all_outfit']);
    Route::get('/outfit/last', [QueriesClothesApi::class, 'get_last_outfit']);
    Route::get('/outfit/history/{id}', [QueriesClothesApi::class, 'get_history_outfit_by_id']);
    Route::get('/outfit/by/{id}', [QueriesClothesApi::class, 'get_outfit_by_id']);
    Route::delete('/outfit/history/by/{id}', [CommandClothesApi::class, 'hard_delete_used_outfit_by_id']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/clothes/yearly/{ctx}', [QueriesStatsApi::class, 'get_stats_yearly_context']);
    Route::get('/clothes/monthly/used/{year}', [QueriesStatsApi::class, 'get_stats_clothes_monthly_used']);
    Route::get('/calendar/{month}/{year}', [QueriesStatsApi::class, 'get_stats_calendar']);
    Route::get('/calendar/detail/date/{date}', [QueriesStatsApi::class, 'get_stats_calendar_by_date']);
    Route::get('/outfit/most/used/{year}', [QueriesStatsApi::class, 'get_stats_outfit_yearly_most_used']);
    Route::get('/wash/summary', [QueriesStatsApi::class, 'get_stats_wash_summary']);
    Route::get('/clothes/most/used/daily', [QueriesStatsApi::class, 'get_stats_most_used_clothes_daily']);
});

Route::prefix('/v1/stats')->group(function () {
    Route::get('/clothes/summary', [QueriesStatsApi::class, 'get_stats_summary']);
    Route::post('/clothes/by/{ctx}', [CommandStatsApi::class, 'get_stats_clothes_most_context']);
    Route::get('/clothes/monthly/created_buyed/{year}', [QueriesStatsApi::class, 'get_stats_clothes_monthly_created_buyed']);
    Route::get('/outfit/monthly/by_outfit/{year}/{outfit_id}', [QueriesStatsApi::class, 'get_stats_outfit_monthly_by_outfit_id']);
});

Route::prefix('/v1/chat')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [CommandChatApi::class, 'post_chat']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'get_all_error']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryApi::class, 'get_all_history']);
    Route::delete('/destroy/{id}', [CommandHistoryApi::class, 'hard_delete_history_by_id']);
});

Route::prefix('/v1/dct')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{type}', [QueriesDictionaryApi::class, 'get_dct_by_type']);
    Route::get('/clothes/category_type', [QueriesDictionaryApi::class, 'get_category_type_clothes']);
    Route::post('/', [CommandDictionaryApi::class, 'post_dct']);
    Route::delete('/{id}', [CommandDictionaryApi::class, 'hard_delete_dct_by_id']);
});

Route::prefix('/v1/feedback')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesFeedbackApi::class, 'get_all_feedback']);
    Route::post('/', [CommandFeedbackApi::class, 'post_feedback']);
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/my', [QueriesUserApi::class, 'get_my_profile']);
    Route::get('/my_year', [QueriesUserApi::class, 'get_my_available_year_filter']);
    Route::put('/fcm', [CommandUserApi::class, 'update_user_fcm']);
});

Route::prefix('/v1/export')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/clothes/excel', [QueriesExportApi::class, 'get_export_clothes_excel']);
    Route::get('/clothes/calendar/excel/{year}', [QueriesExportApi::class, 'get_export_clothes_calendar_excel']);
    Route::get('/clothes/calendar/pdf/{date}', [QueriesExportApi::class, 'get_export_clothes_calendar_daily_pdf']);
    Route::get('/clothes/detail/pdf/{id}', [QueriesExportApi::class, 'get_export_clothes_detail_by_id_pdf']);
    Route::get('/clothes_used/excel', [QueriesExportApi::class, 'get_export_clothes_used_excel']);
    Route::get('/wash/excel', [QueriesExportApi::class, 'get_export_wash_excel']);
    Route::get('/history/excel', [QueriesExportApi::class, 'get_export_history_excel']);
});