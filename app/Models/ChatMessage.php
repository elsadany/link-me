<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $appends=['filePath'];
    function sender(){
        return $this->belongsTo(User::class,'sender_id');
    }
    function getFilePathAttribute(){
        if($this->media_name)
        return url($this->media_name);
        return '';
    }

}
