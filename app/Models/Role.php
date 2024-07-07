<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $with=['permissions'];

    function permissions(){
        return $this->hasMany(AdminRole::class,'role_id');
    }
}
