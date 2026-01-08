<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller
use App\Http\Controllers\Api\AuthApi\Commands as CommandAuthController;
use App\Http\Controllers\Api\ClothesApi\Commands as CommandClothesController;
use App\Http\Controllers\Api\ClothesApi\Queries as QueriesClothesController;
use App\Http\Controllers\Api\OutfitApi\Commands as CommandOutfitController;
use App\Http\Controllers\Api\OutfitApi\Queries as QueriesOutfitController;
use App\Http\Controllers\Api\FeedbackApi\Commands as CommandFeedbackController;
use App\Http\Controllers\Api\FeedbackApi\Queries as QueriesFeedbackController;
use App\Http\Controllers\Api\DictionaryApi\Queries as QueriesDictionaryController;
use App\Http\Controllers\Api\DictionaryApi\Commands as CommandDictionaryController;
use App\Http\Controllers\Api\HistoryApi\Queries as QueriesHistoryController;
use App\Http\Controllers\Api\HistoryApi\Commands as CommandHistoryController;
use App\Http\Controllers\Api\ErrorApi\Queries as QueriesErrorController;
use App\Http\Controllers\Api\ErrorApi\Commands as CommandsErrorController;
use App\Http\Controllers\Api\StatsApi\Commands as CommandStatsController;
use App\Http\Controllers\Api\StatsApi\Queries as QueriesStatsController;
use App\Http\Controllers\Api\UserApi\Queries as QueriesUserController;
use App\Http\Controllers\Api\UserApi\Commands as CommandUserController;
use App\Http\Controllers\Api\ChatApi\Commands as CommandChatController;
use App\Http\Controllers\Api\ExportApi\Queries as QueriesExportController;
use App\Http\Controllers\Api\QuestionApi\Queries as QueriesQuestionController;
use App\Http\Controllers\Api\QuestionApi\Commands as CommandQuestionController;

######################### Public Route #########################

Route::post('/v1/login', [CommandAuthController::class, 'postLogin']);

Route::prefix('/v1/register')->group(function () {
    Route::post('/', [CommandAuthController::class, 'postRegister']);
    Route::post('/validate', [CommandAuthController::class, 'postValidateRegister']);
});

Route::prefix('/v1/question')->group(function () {
    Route::get('/faq', [QueriesQuestionController::class, 'getQuestionFAQ']);
    Route::post('/', [CommandQuestionController::class, 'postCreateQuestion']);
});

Route::prefix('/v1/stats')->group(function () {
    Route::get('/all', [QueriesStatsController::class, 'getAppsSummary']);
    Route::get('/feedback/top', [QueriesStatsController::class, 'getTopFeedback']);
    Route::prefix('/clothes')->group(function () {
        Route::get('/yearly/{ctx}', [QueriesStatsController::class, 'getStatsYearlyContext']);
        Route::get('/summary', [QueriesStatsController::class, 'getStatsSummary']);
        Route::post('/by/{ctx}', [CommandStatsController::class, 'getStatsClothesMostContext']);
        Route::get('/monthly/created_buyed/{year}', [QueriesStatsController::class, 'getStatsClothesMonthlyCreatedBuyed']);
    });
    Route::prefix('/outfit')->group(function () {
        Route::get('/monthly/by_outfit/{year}/{outfit_id}', [QueriesStatsController::class, 'getStatsOutfitMonthlyByOutfitID']);
        Route::get('/most/used/{year}', [QueriesStatsController::class, 'getStatsOutfitYearlyMostUsed']);
    });
});

######################### Private Route #########################

Route::get('/v1/logout', [CommandAuthController::class, 'postLogout'])->middleware(['auth:sanctum']);

Route::prefix('/v1/clothes')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/header/{category}/{order}', [QueriesClothesController::class, 'getAllClothesHeader']);
    Route::get('/detail/{category}/{order}', [QueriesClothesController::class, 'getAllClothesDetail']);
    Route::get('/detail/{clothes_id}', [QueriesClothesController::class, 'getClothesDetailByID']);
    Route::get('/trash', [QueriesClothesController::class, 'getDeletedClothes']);
    Route::post('/', [CommandClothesController::class, 'postCreateClothes']);
    Route::put('/recover/{id}', [CommandClothesController::class, 'putRecoverClothesByID']);
    Route::delete('/delete/{id}', [CommandClothesController::class, 'softDeleteClothesByID']);
    Route::delete('/destroy/{id}', [CommandClothesController::class, 'hardDeleteClothesByID']);
    Route::prefix('/history')->group(function () {
        Route::get('/{clothes_id}/{order}', [QueriesClothesController::class, 'getClothesUsedHistory']);
        Route::get('/last', [QueriesClothesController::class, 'getLastHistory']);
        Route::post('/', [CommandClothesController::class, 'postCreateHistoryClothes']);
        Route::delete('/used/{id}', [CommandClothesController::class, 'hardDeleteClothesUsedByID']);
    });
    Route::prefix('/wash')->group(function () {
        Route::get('/checkpoint/{clothes_id}', [QueriesClothesController::class, 'getWashCheckpointByClothesID']);
        Route::get('/history', [QueriesClothesController::class, 'getAllWashHistory']);
        Route::get('/unfinished', [QueriesClothesController::class, 'getUnfinishedWash']);
        Route::get('/check/{clothes_id}', [QueriesClothesController::class, 'getClothesWashStatusByClothesID']);
        Route::post('/', [CommandClothesController::class, 'postCreateWash']);
        Route::put('/update_checkpoint/{id}', [CommandClothesController::class, 'putUpdateWashByClothesID']);
        Route::delete('/destroy/{id}', [CommandClothesController::class, 'hardDeleteWashByID']);
    });
    Route::prefix('/schedule')->group(function () {
        Route::get('/{day}', [QueriesClothesController::class, 'getScheduleByDay']);
        Route::get('/tomorrow/{day}', [QueriesClothesController::class, 'getScheduleTomorrow']);
        Route::post('/', [CommandClothesController::class, 'postCreateSchedule']);
        Route::delete('/destroy/{id}', [CommandClothesController::class, 'hardDeleteScheduleByID']);
    });
    Route::prefix('/outfit')->group(function () {
        Route::get('/', [QueriesOutfitController::class, 'getAllOutfit']);
        Route::get('/last', [QueriesOutfitController::class, 'getLastOutfit']);
        Route::get('/summary', [QueriesOutfitController::class, 'getOutfitSummary']);
        Route::get('/history/{id}', [QueriesClothesController::class, 'getHistoryOutfitByID']);
        Route::get('/by/{id}', [QueriesOutfitController::class, 'getOutfitByID']);
        Route::post('/generate', [CommandClothesController::class, 'postGeneratedOutfit']);
        Route::post('/save', [CommandOutfitController::class, 'postCreateOutfit']);
        Route::post('/save/clothes', [CommandOutfitController::class, 'postCreateClothesOutfit']);
        Route::post('/history/save', [CommandOutfitController::class, 'postCreateOutfitHistory']);
        Route::delete('/remove/{clothes_id}/{outfit_id}', [CommandOutfitController::class, 'hardDeleteClothesOutfitByID']);
        Route::delete('/history/by/{id}', [CommandClothesController::class, 'hardDeleteUsedOutfitByID']);
    });
    Route::get('/similiar/{ctx}/{val}/{exc}', [QueriesClothesController::class, 'getClothesSimiliarBy']);
});

Route::prefix('/v1/stats')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/clothes/monthly/used/{year}', [QueriesStatsController::class, 'getStatsClothesMonthlyUsed']);
    Route::prefix('/calendar')->group(function () {
        Route::get('/{month}/{year}', [QueriesStatsController::class, 'getStatsCalendar']);
        Route::get('/detail/date/{date}', [QueriesStatsController::class, 'getStatsCalendarByDate']);
    });
    Route::get('/wash/summary', [QueriesStatsController::class, 'getStatsWashSummary']);
    Route::get('/clothes/most/used/daily', [QueriesStatsController::class, 'getStatsMostUsedClothesDaily']);
});

Route::prefix('/v1/chat')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [CommandChatController::class, 'postCreateChat']);
});

Route::prefix('/v1/error')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesErrorController::class, 'getAllError']);
    Route::delete('/destroy/{id}', [CommandsErrorController::class, 'hardDeleteErrorById']);
});

Route::prefix('/v1/history')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesHistoryController::class, 'getAllHistory']);
    Route::delete('/destroy/{id}', [CommandHistoryController::class, 'hardDeleteHistoryByID']);
});

Route::prefix('/v1/dct')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{type}', [QueriesDictionaryController::class, 'getDctByType']);
    Route::get('/clothes/category_type', [QueriesDictionaryController::class, 'getCategoryTypeClothes']);
    Route::post('/', [CommandDictionaryController::class, 'postCreateDct']);
    Route::delete('/{id}', [CommandDictionaryController::class, 'hardDeleteDctByID']);
});

Route::prefix('/v1/feedback')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QueriesFeedbackController::class, 'getAllFeedback']);
    Route::post('/', [CommandFeedbackController::class, 'postCreateFeedback']);
});

Route::prefix('/v1/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/my', [QueriesUserController::class, 'getMyProfile']);
    Route::get('/my_year', [QueriesUserController::class, 'getMyAvailableYearFilter']);
    Route::put('/fcm', [CommandUserController::class, 'putUpdateUserFcm']);
});

Route::prefix('/v1/export')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/clothes')->group(function () {
        Route::get('/excel', [QueriesExportController::class, 'getExportClothesExcel']);
        Route::get('/detail/pdf/{id}', [QueriesExportController::class, 'getExportClothesDetailByIDPdf']);
        Route::get('/used/excel', [QueriesExportController::class, 'getExportClothesUsedExcel']);
        Route::prefix('/calendar')->group(function () {
            Route::get('/excel/{year}', [QueriesExportController::class, 'getExportClothesCalendarExcel']);
            Route::get('/pdf/{date}', [QueriesExportController::class, 'getExportClothesCalendarDailyPdf']);
        });
    });
    Route::prefix('/wash')->group(function () {
        Route::get('/excel', [QueriesExportController::class, 'getExportWashExcel']);
    });
    Route::prefix('/history')->group(function () {
        Route::get('/excel', [QueriesExportController::class, 'getExportHistoryExcel']);
    });
});