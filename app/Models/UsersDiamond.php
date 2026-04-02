<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersDiamond extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    /**
     * Net diamond balance (earned type 1 minus spent type 0) in one query.
     */
    public static function netBalanceForUser(int $userId): int
    {
        $row = static::query()
            ->where('user_id', $userId)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = 1 THEN diamonds ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN type = 0 THEN diamonds ELSE 0 END), 0) as net'
            )
            ->first();

        return (int) ($row->net ?? 0);
    }

    function user(){
        return $this->belongsTo(User::class);

    }
    function product(){
        return $this->belongsTo(Product::class);
    }
}
