<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['firstUser','secondUser','message','messages','bookmarked'];
    protected $appends=['unread','is_blocked'];
    protected $casts=[
        'is_blocked'=>'boolean',
        'id'=>'integer'

    ];
    function secondUser(){
        return $this->belongsTo(User::class,'second_user_id');
    }
    function firstUser(){
        return $this->belongsTo(User::class,'first_user_id');
    }
    function messages(){
        return $this->hasMany(ChatMessage::class,'chat_id')->latest('id');
    }
    function message(){
        return $this->hasOne(ChatMessage::class,'chat_id')->latest('id');
    }
    function getUnreadAttribute(){
        return $this->messages()->where('sender_id','!=',auth()->guard('sanctum')->user()->id)->where('read',0)->count();
    }
    function bookmarked(){
        if($this->first_user_id==auth()->guard('sanctum')->user()->id){
            return $this->hasOne(ChatMessage::class,'chat_id')->where('bookmark_from_first_user',1)->latest('id');
        }else{
            return $this->hasOne(ChatMessage::class,'chat_id')->where('bookmark_from_second_user',1)->latest('id');

        }
    }
    function getIsBlockedAttribute(){
        $user_block=UserBlock::where(['user_id'=>$this->first_user_id,'friend_id'=>$this->second_user_id])
            ->orWhere(['friend_id'=>$this->first_user_id,'user_id'=>$this->second_user_id])->first();
        if(is_object($user_block))
            return 1;
        return 0;
    }
}

