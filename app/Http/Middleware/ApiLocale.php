<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        if (auth()->guard('sanctum')->check()) {
            $user = auth()->guard('sanctum')->user();
            if ($user->is_active == 0) {
                $user->tokens()->delete();
            } else {
                // Gate DB writes: only one UPDATE per user per 2 minutes (ConvertToOffline* still reads DB).
                // Cache::add is atomic on Redis and avoids parsing last_availablity on every request.
                $key = 'user:last_availablity:'.$user->id;
                if (Cache::add($key, true, 120)) {
                    $user->update(['last_availablity' => Carbon::now()]);
                }
            }
        }
        return $next($request);
    }
}
