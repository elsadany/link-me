<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded=['id'];
    function replies(){
        return $this->hasMany(TicketsReply::class,'ticket_id');
    }
}
