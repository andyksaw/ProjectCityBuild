<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Webhook subscribers
Route::prefix('webhooks')->group(function () {
    Route::post('stripe', 'WebhookController@stripe');
});

Route::prefix('bans')->group(function () {
    Route::post('list', 'GameBanController@getBanList');
    Route::post('store/ban', 'GameBanController@storeBan');
    Route::post('store/unban', 'GameBanController@storeUnban');
    Route::post('status', 'GameBanController@getPlayerStatus');
});

Route::prefix('auth')->group(function () {
    Route::post('minecraft', [
        'as' => 'auth.minecraft.store',
        'uses' => 'MinecraftAuthTokenController@store',
    ]);
    Route::get('minecraft/{minecraftUUID}', [
        'as' => 'auth.minecraft.show',
        'uses' => 'MinecraftAuthTokenController@show',
    ]);
});

Route::prefix('donations')->group(function () {
    Route::get('create', [
        'as' => 'donations.create',
        'uses' => 'DonationController@create',
    ]);
});

Route::prefix('groups')->group(function () {
    Route::get('/', 'GroupApiController@getAll');
});

Route::post('discord/sync', 'DiscordSyncController@getRank');
