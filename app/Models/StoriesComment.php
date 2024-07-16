<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoriesComment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
//    protected $with=['user'];
    function user(){
        if($this->is_owner==0)
        return $this->belongsTo(User::class,'user_id');
        else
            return null;
    }
}
