<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersParchase extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['plan'];

    public static function activeSubscriptionForUser(int $userId): ?self
    {
        $now = Carbon::now('Asia/Riyadh');

        return static::query()
            ->where('user_id', $userId)
            ->where('finish_at', '>=', $now)
            ->latest('id')
            ->first();
    }

    public static function userHasActiveSubscription(int $userId): bool
    {
        $now = Carbon::now('Asia/Riyadh');

        return static::query()
            ->where('user_id', $userId)
            ->where('finish_at', '>=', $now)
            ->exists();
    }

    function plan(){
        return $this->belongsTo(SupscriptionPlan::class,'subscription_plan_id');
    }
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
