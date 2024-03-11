<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendFcmNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $users;
    public $title;
    public $category;
    public $message;
    public $extra;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($users, $title, $message, $extra=[],$category=null)
    {
        $this->users = $users;
        $this->title = $title;
        $this->message = $message;
        $this->extra = $extra;
        $this->category=$category;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
