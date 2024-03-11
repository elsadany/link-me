<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:user')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('local')->group(function () {
    Route::post('send-verify-code', [\App\Http\Controllers\apis\AuthApi::class, 'sendCode']);
    Route::post('verify-code', [\App\Http\Controllers\apis\AuthApi::class, 'verifyCode']);
    Route::post('register', [\App\Http\Controllers\apis\AuthApi::class, 'register']);
    Route::post('visitor/register', [\App\Http\Controllers\apis\AuthApi::class, 'registerVisitor']);
    Route::post('login', [\App\Http\Controllers\apis\AuthApi::class, 'login']);
    Route::post('forget-password', [\App\Http\Controllers\apis\AuthApi::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\apis\AuthApi::class, 'resetPassword']);
    Route::get('delete-reasons',[\App\Http\Controllers\apis\HomeApi::class,'reasons']);
    Route::get('countries',[\App\Http\Controllers\apis\AuthApi::class,'countries']);
    Route::get('settings',[\App\Http\Controllers\apis\HomeApi::class,'appSetting']);
    Route::post('contacts/store',[\App\Http\Controllers\apis\HomeApi::class,'storeContact']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('myaccount', [\App\Http\Controllers\apis\AuthApi::class, 'myaccount']);
        Route::get('mynotifications', [\App\Http\Controllers\apis\AuthApi::class, 'notifications']);
        Route::post('notifications/delete', [\App\Http\Controllers\apis\AuthApi::class, 'deleteNotification']);
        Route::post('notifications/read', [\App\Http\Controllers\apis\AuthApi::class, 'readNotification']);
        Route::post('update-password', [\App\Http\Controllers\apis\AuthApi::class, 'updatePassword']);
        Route::post('update-setting', [\App\Http\Controllers\apis\AuthApi::class, 'updateShow']);
        Route::any('update-fcm-token', [\App\Http\Controllers\apis\AuthApi::class, 'updateFcmToken']);
        Route::post('update-profile', [\App\Http\Controllers\apis\AuthApi::class, 'updateProfile']);
        Route::post('update-email', [\App\Http\Controllers\apis\AuthApi::class, 'changeEmailSendCode']);
        Route::post('confirm-update-email', [\App\Http\Controllers\apis\AuthApi::class, 'confirmChangeEmail']);
        Route::post('complete-profile', [\App\Http\Controllers\apis\AuthApi::class, 'completeProfile']);
        Route::post('toggle-available', [\App\Http\Controllers\apis\AuthApi::class, 'toggleAvailabe']);
        Route::post('toggle-online', [\App\Http\Controllers\apis\AuthApi::class, 'toggleOnline']);
        Route::post('toggle-link', [\App\Http\Controllers\apis\AuthApi::class, 'toggleLink']);
        Route::post('delete-account',[\App\Http\Controllers\apis\AuthApi::class,'deleteAccount']);
        Route::post('tickets/store',[\App\Http\Controllers\apis\HomeApi::class,'storeTicket']);
        Route::post('tickets/store-reply',[\App\Http\Controllers\apis\HomeApi::class,'postReply']);
        Route::get('tickets',[\App\Http\Controllers\apis\HomeApi::class,'mytickets']);
        Route::post('story/add',[\App\Http\Controllers\apis\HomeApi::class,'addStory']);
        Route::get('story/all',[\App\Http\Controllers\apis\HomeApi::class,'getStories']);
        Route::post('story/delete',[\App\Http\Controllers\apis\HomeApi::class,'deleteStory']);
        Route::post('story/toggle-like',[\App\Http\Controllers\apis\HomeApi::class,'addToggleLike']);
        Route::post('story/add-comment',[\App\Http\Controllers\apis\HomeApi::class,'addComment']);
        Route::post('story/delete-comment',[\App\Http\Controllers\apis\HomeApi::class,'deleteComment']);
        Route::post('story/report',[\App\Http\Controllers\apis\HomeApi::class,'reportStory']);
        Route::get('supscription/plans',[\App\Http\Controllers\apis\SupscriptionApi::class,'plans']);
        Route::get('supscription/products',[\App\Http\Controllers\apis\SupscriptionApi::class,'products']);
        Route::get('supscription/stars',[\App\Http\Controllers\apis\SupscriptionApi::class,'stars']);
        Route::get('supscription/status',[\App\Http\Controllers\apis\SupscriptionApi::class,'getSupscriptionStatus']);
        Route::get('supscription/last',[\App\Http\Controllers\apis\SupscriptionApi::class,'getLastSupscription']);
        Route::post('supscription/subscribe',[\App\Http\Controllers\apis\SupscriptionApi::class,'buySubscription']);
        Route::post('supscription/buy-diamonds',[\App\Http\Controllers\apis\SupscriptionApi::class,'buyDiamonds']);
        Route::post('supscription/buy-stars',[\App\Http\Controllers\apis\SupscriptionApi::class,'buyStar']);
        Route::get('supscription/diamonds',[\App\Http\Controllers\apis\SupscriptionApi::class,'getDiamonds']);
        Route::get('top-users',[\App\Http\Controllers\apis\SupscriptionApi::class,'topUsers']);
        Route::get('top-users/remaining',[\App\Http\Controllers\apis\SupscriptionApi::class,'remainingTimes']);
        Route::post('search',[\App\Http\Controllers\apis\AuthApi::class,'search']);
        Route::post('submit-request',[\App\Http\Controllers\apis\ChatsApi::class,'sendChatRequest']);
        Route::post('chat-requests',[\App\Http\Controllers\apis\ChatsApi::class,'chats']);
        Route::post('chats/delete',[\App\Http\Controllers\apis\ChatsApi::class,'deleteChat']);
        Route::post('one-chat',[\App\Http\Controllers\apis\ChatsApi::class,'oneChat']);
        Route::get('one-user',[\App\Http\Controllers\apis\AuthApi::class,'showUser']);
        Route::post('chat-request/accept',[\App\Http\Controllers\apis\ChatsApi::class,'accept']);
        Route::post('chat-request/refuse',[\App\Http\Controllers\apis\ChatsApi::class,'refuse']);
        Route::post('chat-request/send-message',[\App\Http\Controllers\apis\ChatsApi::class,'sendMessage']);
        Route::post('chat/hide-message',[\App\Http\Controllers\apis\ChatsApi::class,'hideMessage']);
        Route::post('chat/delete-message',[\App\Http\Controllers\apis\ChatsApi::class,'deleteMessage']);
        Route::post('chats/delete-chat',[\App\Http\Controllers\apis\ChatsApi::class,'deleteChat']);
        Route::post('chats/bookmark-message',[\App\Http\Controllers\apis\ChatsApi::class,'boomarkMassege']);
        Route::post('chats/report-message',[\App\Http\Controllers\apis\ChatsApi::class,'reportMassege']);
        Route::post('users/block-user',[\App\Http\Controllers\apis\HomeApi::class,'blockUser']);
        Route::post('users/report-user',[\App\Http\Controllers\apis\HomeApi::class,'reportUser']);
        Route::post('users/un-friend',[\App\Http\Controllers\apis\HomeApi::class,'unFriend']);
        Route::get('users/blocks',[\App\Http\Controllers\apis\HomeApi::class,'blockedUsers']);
        Route::post('users/blocks/delete',[\App\Http\Controllers\apis\HomeApi::class,'deleteBlock']);
        Route::get('friends',[\App\Http\Controllers\apis\HomeApi::class,'getFriends']);
        Route::post('friends/delete',[\App\Http\Controllers\apis\HomeApi::class,'deleteFriend']);

    });
});
include __DIR__.'/adminApi.php';
