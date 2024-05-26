<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoriesReport extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    function story(){
        return $this->belongsTo(UsersStory::class,'story_id');
    }
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
