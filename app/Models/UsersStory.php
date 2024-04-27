<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersStory extends Model
{
    protected $guarded=['id'];
    use HasFactory;
    protected $appends=['video','likes','comments','is_like'];
    protected $with=['comments'];
    function getVideoAttribute(){
        if($this->file!='')
            return url($this->file);
        return '';
    }
    function likes(){
        return $this->hasMany(StoriesLike::class,'story_id');
    }
    function comments(){
        return $this->hasMany(StoriesComment::class,'story_id');
    }
    function getLikesAttribute(){
        return $this->likes()->count();
    }
    function getCommentsAttribute(){
        return $this->comments()->count();
    }
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    function getIsLikeAttribute(){
        if(auth()->guard('sanctum')->check())
        return $this->likes()->where('user_id',auth()->guard('sanctum')->user()->id)->first()?1:0;
        else
            return 0;
    }
}
