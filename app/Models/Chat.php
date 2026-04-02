<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Chat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /** Avoid eager-loading all messages on every chat (was N+1 / memory heavy). */
    protected $with = [];

    protected $casts = [
        'is_blocked' => 'boolean',
        'id' => 'integer',
    ];

    function secondUser()
    {
        return $this->belongsTo(User::class, 'second_user_id');
    }

    function firstUser()
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_id')->latest('id');
    }

    function message()
    {
        return $this->hasOne(ChatMessage::class, 'chat_id')->latest('id');
    }

    function bookmarked()
    {
        $user = auth()->guard('sanctum')->user();
        if (! $user) {
            return $this->hasOne(ChatMessage::class, 'chat_id')->whereRaw('0 = 1');
        }
        if ($this->first_user_id == $user->id) {
            return $this->hasOne(ChatMessage::class, 'chat_id')->where('bookmark_from_first_user', 1)->latest('id');
        }

        return $this->hasOne(ChatMessage::class, 'chat_id')->where('bookmark_from_second_user', 1)->latest('id');
    }

    /**
     * List view: participants, last message preview, unread count — no full messages collection.
     */
    public function scopeWithApiListAttributes(Builder $query): Builder
    {
        $userId = auth()->guard('sanctum')->id();

        $query->with(['firstUser', 'secondUser', 'message']);

        if ($userId) {
            $query->withCount([
                'messages as unread' => function ($q) use ($userId) {
                    $q->where('sender_id', '!=', $userId)->where('read', 0);
                },
            ]);
        }

        return $query;
    }

    /**
     * Single-query “other user blocked me / I blocked them” flag for chat list payloads.
     */
    public static function hydrateBlockedForChats(Collection $chats, int $authId): void
    {
        if ($chats->isEmpty()) {
            return;
        }

        $rows = UserBlock::query()
            ->where('user_id', $authId)
            ->orWhere('friend_id', $authId)
            ->get(['user_id', 'friend_id']);

        $blockedOtherIds = $rows->map(function ($b) use ($authId) {
            return (int) ($b->user_id == $authId ? $b->friend_id : $b->user_id);
        })->flip();

        foreach ($chats as $chat) {
            $otherId = (int) ($chat->first_user_id == $authId ? $chat->second_user_id : $chat->first_user_id);
            $chat->setAttribute('is_blocked', $blockedOtherIds->has($otherId) ? 1 : 0);
        }
    }
}
