<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersParchase extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['plan'];
    function plan(){
        return $this->belongsTo(SupscriptionPlan::class,'subscription_plan_id');
    }
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
