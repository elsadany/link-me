<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $guarded=[];
    protected $appends=['about','privacy','terms'];
    function getAboutAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->about_ar;
        return $this->about_en;
    }
    function getPrivacyAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->privacy_ar;
        return $this->privacy_en;
    }
    function getTermsAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->terms_ar;
        return $this->terms_en;
    }
    function getAboutStarAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->about_star_ar;
        return $this->about_star_en;
    }

}
