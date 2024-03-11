<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $hidden=['country_enNationality','country_arNationality','country_code','created_at','updated_at'];
    protected $appends=['lang'];
    function getLangAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->country_arName;
        return $this->country_enName;
    }

}
