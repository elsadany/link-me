<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersStory extends Model
{
    protected $guarded=['id'];
    use HasFactory;

    /** Hide counter columns; `likes` append and `comments` relation still serialize. */
    protected $hidden = ['likes_count', 'comments_count'];

    protected $appends=['video','likes','is_like','is_read'];

    /**
     * Eager loads used by story feeds — do not use global $with (avoids relation/accessor conflicts).
     */
    public function scopeForFeed($query)
    {
        return $query->with([
            'comments' => fn ($q) => $q->with([
                'user' => fn ($u) => $u->select('id', 'name', 'email')->withoutAppends(),
            ]),
        ])->withCount(['likes', 'comments']);
    }

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
        if (array_key_exists('likes_count', $this->attributes)) {
            return (int) $this->attributes['likes_count'];
        }

        return $this->likes()->count();
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
    function getIsReadAttribute(){
        return 0;
    }
}
