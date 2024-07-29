<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    protected $with=['user','replies'];
    function replies(){
        return $this->hasMany(TicketsReply::class,'ticket_id');
    }
    function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
