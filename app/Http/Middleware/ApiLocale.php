<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class ApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {


        if($request->header('locale')=='ar'){
            app()->setLocale('ar');
        }else{
            app()->setLocale('en');
        }
        if(auth()->guard('sanctum')->check()){
            $user=auth()->guard('sanctum')->user();
            if($user->is_active==0){
                $user->tokens()->delete();

            }
            $user->update(['last_availablity'=>Carbon::now()]);
        }
        return $next($request);
    }
}
