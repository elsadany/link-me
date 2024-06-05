<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersDiamond extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    function user(){
        return $this->belongsTo(User::class);

    }
    function product(){
        return $this->belongsTo(Product::class);
    }
}
