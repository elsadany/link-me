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
       Route::post('update-profile',[\App\Http\Controllers\apis\admin\AuthApi::class,'updateProfile']);
       Route::post('update-password',[\App\Http\Controllers\apis\admin\AuthApi::class,'updatePassword']);
       Route::get('countries/toggle-active/{country}',[\App\Http\Controllers\apis\admin\CountriesApi::class,'toggleActive']);
       Route::get('users/toggle-active/{user}',[\App\Http\Controllers\apis\admin\UsersApi::class,'toggleActive']);
       Route::apiResource('countries',\App\Http\Controllers\apis\admin\CountriesApi::class);
       Route::apiResource('admins',\App\Http\Controllers\apis\admin\AdminController::class);
       Route::apiResource('delete-reasons',\App\Http\Controllers\apis\admin\DeleteReasonsApi::class);
       Route::apiResource('products',\App\Http\Controllers\apis\admin\ProductsApi::class);
       Route::apiResource('star-prices',\App\Http\Controllers\apis\admin\StarsPricesApi::class);
       Route::apiResource('supscription-plans',\App\Http\Controllers\apis\admin\SupscriptionsPlansApi::class);
       Route::apiResource('users',\App\Http\Controllers\apis\admin\UsersApi::class);
       Route::apiResource('purchases',\App\Http\Controllers\apis\admin\PurchasesApi::class);
       Route::apiResource('users-stories',\App\Http\Controllers\apis\admin\UsersStoriesApi::class);
    });
});
