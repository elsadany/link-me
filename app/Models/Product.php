<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $appends=['lang'];
    function getLangAttribute()
    {

        if (app()->isLocale('ar'))
            return $this->title_ar;
        return $this->title_en;
    }
}
