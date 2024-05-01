<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFriend extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['friend'];
    protected $appends=['chat_id'];

    function friend(){
        dd($this->user_id,$this->friend_id,$this->id);
        if($this->user_id==auth()->guard('sanctum')->user()->id) {
            dd($this->user_id==auth()->guard('sanctum')->user()->id);
            return $this->belongsTo(User::class, 'friend_id');
        }else {
            dd($this->user_id,auth()->guard('sanctum')->user()->id);
            return $this->belongsTo(User::class, 'user_id');
        }
    }
    function getChatIdAttribute(){
        return optional(Chat::where(['first_user_id'=>$this->user_id,'second_user_id'=>$this->friend_id])
            ->orWhere(['first_user_id'=>$this->friend_id,'second_user_id'=>$this->user_id])->first())->id;
    }
}
