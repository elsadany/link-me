<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [\App\Http\Controllers\TestController::class, 'uploadToSpace']);
Route::get('terms',function (\Illuminate\Http\Request $request){
    if($request->get('locale')=='ar'){
        app()->setLocale('ar');
    }else{
        app()->setLocale('en');
    }
   $setting=\App\Models\AppSetting::first();
   $data['content']=$setting->terms;
    return view('welcome',$data);

});
Route::get('privacy',function (\Illuminate\Http\Request $request){
    if($request->get('locale')=='ar'){
        app()->setLocale('ar');
    }else{
        app()->setLocale('en');
    }
   $setting=\App\Models\AppSetting::first();
   $data['content']=$setting->privacy;
   return view('welcome',$data);
});Route::get('about',function (\Illuminate\Http\Request $request){
    if($request->get('locale')=='ar'){
        app()->setLocale('ar');
    }else{
        app()->setLocale('en');
    }
   $setting=\App\Models\AppSetting::first();
   $data['content']=$setting->about;
    return view('welcome',$data);

});
Route::get('about_star',function (\Illuminate\Http\Request $request){
    if($request->get('locale')=='ar'){
        app()->setLocale('ar');
    }else{
        app()->setLocale('en');
    }
   $setting=\App\Models\AppSetting::first();
   $data['content']=$setting->aboutStar;
    return view('welcome',$data);

});
Route::get('broadcast',function (){
   return \App\Events\Hello::dispatch();
});

