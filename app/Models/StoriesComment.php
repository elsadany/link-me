<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoriesComment extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $appends=['user'];
    function user_object(){
        return $this->belongsTo(User::class,'user_id');
    }
    function getUserAttribute(){
      return [
          'id'=>$this->user_object->id,
          'name'=>$this->user_object->name,
          'imagePath'=>$this->user_object->imagePath,

      ];
    }
}
