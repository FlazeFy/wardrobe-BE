<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller
use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthApi;
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

Route::post('/v1/login', [CommandAuthApi::class, 'postLogin']);

Route::prefix('/v1/register')->group(function () {
    Route::post('/', [CommandAuthApi::class, 'postRegister']);
    Route::post('/validate', [CommandAuthApi::class, 'postValidateRegister']);
});

Route::prefix('/v1/question')->group(function () {
    Route::get('/faq', [QueriesQuestionApi::class, 'getQuestionFAQ']);
    Route::post('/', [CommandQuestionApi::class, 'postQuestion']);
});

Route::prefix('/v1/stats')->group(function () {
    Route::get('/all', [QueriesStatsApi::class, 'getAppsSummary']);
    Route::get('/feedback/top', [QueriesStatsApi::class, 'getTopFeedback']);
    Route::prefix('/clothes')->group(function () {
        Route::get('/yearly/{ctx}', [QueriesStatsApi::class, 'getStatsYearlyContext']);
        Route::get('/summary', [QueriesStatsApi::class, 'getStatsSummary']);
        Route::post('/by/{ctx}', [CommandStatsApi::class, 'getStatsClothesMostContext']);
        Route::get('/monthly/created_buyed/{year}', [QueriesStatsApi::class, 'getStatsClothesMonthlyCreatedBuyed']);
    });
    Route::prefix('/outfit')->group(function () {
        Route::get('/monthly/by_outfit/{year}/{outfit_id}', [QueriesStatsApi::class, 'getStatsOutfitMonthlyByOutfitID']);
        Route::get('/most/used/{year}', [QueriesStatsApi::class, 'getStatsOutfitYearlyMostUsed']);
    });
});

######################### Private Route #########################

Route::get('/v1/logout', [CommandAuthApi::class, 'postLogout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/clothes')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/header/{category}/{order}', [QueriesClothesApi::class, 'getAllClothesHeader']);
    Route::get('/detail/{category}/{order}', [QueriesClothesApi::class, 'getAllClothesDetail']);
    Route::get('/detail/{clothes_id}', [QueriesClothesApi::class, 'getClothesDetailByID']);
    Route::get('/trash', [QueriesClothesApi::class, 'getDeletedClothes']);
    Route::post('/', [CommandClothesApi::class, 'postClothes']);
    Route::put('/recover/{id}', [CommandClothesApi::class, 'recoverClothes_by_id']);
    Route::delete('/delete/{id}', [CommandClothesApi::class, 'softDeleteClothesByID']);
    Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hardDeleteClothesByID']);
    Route::prefix('/history')->group(function () {
        Route::get('/{clothes_id}/{order}', [QueriesClothesApi::class, 'getClothesUsedHistory']);
        Route::get('/last', [QueriesClothesApi::class, 'getLastHistory']);
        Route::post('/', [CommandClothesApi::class, 'postHistoryClothes']);
        Route::delete('/used/{id}', [CommandClothesApi::class, 'hardDeleteClothesUsedByID']);
    });
    Route::prefix('/wash')->group(function () {
        Route::get('/checkpoint/{clothes_id}', [QueriesClothesApi::class, 'getWashCheckpointByClothesID']);
        Route::get('/history', [QueriesClothesApi::class, 'getAllWashHistory']);
        Route::get('/unfinished', [QueriesClothesApi::class, 'getUnfinishedWash']);
        Route::get('/check/{clothes_id}', [QueriesClothesApi::class, 'getClothesWashStatusByClothesID']);
        Route::post('/', [CommandClothesApi::class, 'postWashClothes']);
        Route::put('/update_checkpoint/{id}', [CommandClothesApi::class, 'updateWashByClothesID']);
        Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hardDeleteWashByID']);
    });
    Route::prefix('/schedule')->group(function () {
        Route::get('/{day}', [QueriesClothesApi::class, 'getScheduleByDay']);
        Route::get('/tomorrow/{day}', [QueriesClothesApi::class, 'getScheduleTomorrow']);
        Route::post('/', [CommandClothesApi::class, 'postSchedule']);
        Route::delete('/destroy/{id}', [CommandClothesApi::class, 'hardDeleteScheduleByID']);
    });
    Route::prefix('/outfit')->group(function () {
        Route::get('/', [QueriesClothesApi::class, 'getAllOutfit']);
        Route::get('/last', [QueriesClothesApi::class, 'getLastOutfit']);
        Route::get('/summary', [QueriesClothesApi::class, 'getOutfitSummary']);
        Route::get('/history/{id}', [QueriesClothesApi::class, 'getHistoryOutfitByID']);
        Route::get('/by/{id}', [QueriesClothesApi::class, 'getOutfitByID']);
        Route::post('/generate', [CommandClothesApi::class, 'postGeneratedOutfit']);
        Route::post('/save', [CommandClothesApi::class, 'postSaveOutfit']);
        Route::post('/save/clothes', [CommandClothesApi::class, 'postSaveClothesOutfit']);
        Route::post('/history/save', [CommandClothesApi::class, 'postSaveOutfitHistory']);
        Route::delete('/remove/{clothes_id}/{outfit_id}', [CommandClothesApi::class, 'hardDeleteClothesOutfitByID']);
        Route::delete('/history/by/{id}', [CommandClothesApi::class, 'hardDeleteUsedOutfitByID']);
    });
    Route::get('/similiar/{ctx}/{val}/{exc}', [QueriesClothesApi::class, 'getClothesSimiliarBy']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/clothes/monthly/used/{year}', [QueriesStatsApi::class, 'getStatsClothesMonthlyUsed']);
    Route::prefix('/calendar')->group(function () {
        Route::get('/{month}/{year}', [QueriesStatsApi::class, 'getStatsCalendar']);
        Route::get('/detail/date/{date}', [QueriesStatsApi::class, 'getStatsCalendarByDate']);
    });
    Route::get('/wash/summary', [QueriesStatsApi::class, 'getStatsWashSummary']);
    Route::get('/clothes/most/used/daily', [QueriesStatsApi::class, 'getStatsMostUsedClothesDaily']);
});

Route::prefix('/v1/chat')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [CommandChatApi::class, 'postChat']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'getAllError']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryApi::class, 'getAllHistory']);
    Route::delete('/destroy/{id}', [CommandHistoryApi::class, 'hardDeleteHistoryByID']);
});

Route::prefix('/v1/dct')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{type}', [QueriesDictionaryApi::class, 'getDctByType']);
    Route::get('/clothes/category_type', [QueriesDictionaryApi::class, 'getCategoryTypeClothes']);
    Route::post('/', [CommandDictionaryApi::class, 'postDct']);
    Route::delete('/{id}', [CommandDictionaryApi::class, 'hardDeleteDctByID']);
});

Route::prefix('/v1/feedback')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesFeedbackApi::class, 'getAllFeedback']);
    Route::post('/', [CommandFeedbackApi::class, 'postFeedback']);
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/my', [QueriesUserApi::class, 'getMyProfile']);
    Route::get('/my_year', [QueriesUserApi::class, 'getMyAvailableYearFilter']);
    Route::put('/fcm', [CommandUserApi::class, 'updateUserFcm']);
});

Route::prefix('/v1/export')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/clothes')->group(function () {
        Route::get('/excel', [QueriesExportApi::class, 'getExportClothesExcel']);
        Route::get('/detail/pdf/{id}', [QueriesExportApi::class, 'getExportClothesDetailByIDPdf']);
        Route::get('/used/excel', [QueriesExportApi::class, 'getExportClothesUsedExcel']);
        Route::prefix('/calendar')->group(function () {
            Route::get('/excel/{year}', [QueriesExportApi::class, 'getExportClothesCalendarExcel']);
            Route::get('/pdf/{date}', [QueriesExportApi::class, 'getExportClothesCalendarDailyPdf']);
        });
    });
    Route::prefix('/wash')->group(function () {
        Route::get('/excel', [QueriesExportApi::class, 'getExportWashExcel']);
    });
    Route::prefix('/history')->group(function () {
        Route::get('/excel', [QueriesExportApi::class, 'getExportHistoryExcel']);
    });
});