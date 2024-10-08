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

Route::get('create-admin',function (){

   \App\Models\Admin::create([
       'name'=>'admin',
       'email'=>'super_admin@link-me.live',
       'password'=>\Illuminate\Support\Facades\Hash::make('12345678')
   ]);
});
Route::prefix('admin')->group(function () {
    Route::post('login',[\App\Http\Controllers\apis\admin\AuthApi::class,'login']);
    Route::post('forget-password', [\App\Http\Controllers\apis\admin\AuthApi::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\apis\admin\AuthApi::class, 'resetPassword']);
    Route::middleware('auth:admin')->group(function (){
       Route::get('my-account',[\App\Http\Controllers\apis\admin\AuthApi::class,'myaccount']);
       Route::get('dashboard',[\App\Http\Controllers\apis\admin\AuthApi::class,'dashboard']);
       Route::post('update-profile',[\App\Http\Controllers\apis\admin\AuthApi::class,'updateProfile']);
       Route::post('update-password',[\App\Http\Controllers\apis\admin\AuthApi::class,'updatePassword']);
       Route::get('countries/toggle-active/{country}',[\App\Http\Controllers\apis\admin\CountriesApi::class,'toggleActive']);
       Route::apiResource('countries',\App\Http\Controllers\apis\admin\CountriesApi::class);
       Route::get('contacts/toggle-read/{contact}',[\App\Http\Controllers\apis\admin\ContactsApi::class,'toggleRead']);
       Route::get('users/toggle-active/{user}',[\App\Http\Controllers\apis\admin\UsersApi::class,'toggleActive']);
       Route::apiResource('tickets',\App\Http\Controllers\apis\admin\TicketsApi::class);
       Route::apiResource('contacts',\App\Http\Controllers\apis\admin\ContactsApi::class);
       Route::apiResource('settings',\App\Http\Controllers\apis\admin\SettingsApi::class);
       Route::apiResource('admins',\App\Http\Controllers\apis\admin\AdminController::class);
       Route::apiResource('delete-reasons',\App\Http\Controllers\apis\admin\DeleteReasonsApi::class);
       Route::apiResource('products',\App\Http\Controllers\apis\admin\ProductsApi::class);
       Route::apiResource('star-prices',\App\Http\Controllers\apis\admin\StarsPricesApi::class);
       Route::apiResource('supscription-plans',\App\Http\Controllers\apis\admin\SupscriptionsPlansApi::class);
       Route::apiResource('users',\App\Http\Controllers\apis\admin\UsersApi::class);
       Route::get('purchases/get-diamonds',[\App\Http\Controllers\apis\admin\PurchasesApi::class,'diamonds']);
       Route::apiResource('purchases',\App\Http\Controllers\apis\admin\PurchasesApi::class);
       Route::get('users-reports',[\App\Http\Controllers\apis\admin\UsersApi::class,'reports']);
       Route::get('user-stories-reports',[\App\Http\Controllers\apis\admin\UsersStoriesApi::class,'reports']);
       Route::get('user-stories/toggle-active/{userStory}',[\App\Http\Controllers\apis\admin\UsersStoriesApi::class,'toggleActive']);
       Route::apiResource('users-stories',\App\Http\Controllers\apis\admin\UsersStoriesApi::class);
        //roles
        Route::get('roles', [\App\Http\Controllers\apis\admin\RolesController::class, 'index']);
        Route::post('roles/store', [\App\Http\Controllers\apis\admin\RolesController::class, 'store']);
        Route::get('roles/show/{role}', [\App\Http\Controllers\apis\admin\RolesController::class, 'show']);
        Route::post('roles/update/{role}', [\App\Http\Controllers\apis\admin\RolesController::class, 'update']);
        Route::delete('roles/delete/{role}', [\App\Http\Controllers\apis\admin\RolesController::class, 'destroy']);

    });
});
