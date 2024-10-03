<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Chat implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $chat_id;
    public $user_id;
    public $reciever_id;
    public $user_name;
    public $user_image;
    public $message;
    public $type;
    public $media_type;
    public $file;
    public $created_at;
    public $one_time;
    public $message_id;

    public function __construct($chat_id, $user_id,$reciever_id, $user_name, $user_image, $message,$type,$media_type,$file,$created_at,$one_time=0,$message_id)
    {
        $this->chat_id = $chat_id;
        $this->user_id = $user_id;
        $this->reciever_id = $reciever_id;
        $this->user_name = $user_name;
        $this->user_image = $user_image;
        $this->message = $message;
        $this->type=$type;
        $this->media_type=$media_type;
        $this->file=$file;
        $this->created_at=$created_at;
        $this->one_time=$one_time;
        $this->message_id=$message_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chat-' . $this->chat_id),
        ];
    }

    public function broadcastWith(): array
    {
       return [
           'chat_id'=>$this->chat_id,
           'user_id'=>$this->user_id,
           'user_name'=>$this->user_name,
           'user_image'=>$this->user_image,
           'message'=>$this->message,
           'type'=>$this->type,
           'media_type'=>$this->media_type,
           'file'=>$this->file,
           'created_at'=>$this->created_at,
           'one_time'=>$this->one_time,
           'message_id'=>$this->message_id
       ];
    }
    public function broadcastAs(): string
    {
        return 'message-sent';
    }
}
