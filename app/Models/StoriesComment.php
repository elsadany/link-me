<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoriesComment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['user'];
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
