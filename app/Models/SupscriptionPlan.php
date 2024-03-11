<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupscriptionPlan extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['features'];
    function features(){
        return $this->hasMany(SupscriptionPlanFeatures::class,'supscription_plan_id');
    }
    protected $appends=['lang'];
    function getLangAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->name_ar;
        return $this->name_en;
    }
}
