<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    protected $appends = ['imagePath','likes', 'is_profile_completed','links', 'country', 'sent_tickets', 'unread_tickets', 'canAddStory', 'followers','is_blocked','is_follower'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $casts=[
        'is_blocked'=>'boolean',
        'is_follower'=>'boolean',
        'email_verified_at' => 'datetime',
        'country_id' => 'integer'
    ];
    function getImagePathAttribute()
    {
        if ($this->image != '')
            return url($this->image);
        if($this->type=='visitor')
            return url('Group3336.png');
        return 'https://www.w3schools.com/w3css/img_avatar2.png';
    }

    function getIsProfileCompletedAttribute()
    {
        if ($this->name == '' || $this->country_id == '' || $this->bio == '' || $this->gander == '')
            return 0;
        return 1;
    }

    function getCountryAttribute()
    {
        $country = Country::find($this->country_id);
        if (is_object($country))
            return $country->lang;
        return '';
    }

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at', 'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    public function ticketReplies()
    {
        return $this->morphMany(TicketsReply::class, 'replaibale');
    }

    function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    function stories()
    {
        return $this->hasMany(UsersStory::class, 'user_id')->where('expire_at', '>', Carbon::now());
    }

    function getNotificationsAttribute()
    {
        return 0;
    }

    function getSentTicketsAttribute()
    {
        return $this->tickets()->where('is_read', 1)->count();
    }

    function getUnreadTicketsAttribute()
    {
        return $this->tickets()->where('is_read', 0)->count();
    }

    function getFollowersAttribute()
    {

        return UserFriend::where( 'friend_id' ,$this->id)
            ->orWhere('user_id' , $this->id)->count();

    }

    function getCanAddStoryAttribute()
    {
        $purchase=UsersParchase::latest()->where('user_id',$this->id)->where('finish_at','>=',Carbon::now())->first();
        $story = UsersStory::where('user_id', $this->id)->whereDate('expire_at', '>', Carbon::now())->first();
        if (is_object($story)&&!is_object($purchase))
            return 0;
        return 1;
    }

    function getIsBlockedAttribute()
    {
        if (auth()->guard('sanctum')->check()) {
        $user_block = UserBlock::where(['user_id' => auth()->guard('sanctum')->user()->id, 'friend_id' => $this->id])->first();
        if (is_object($user_block))
            return 1;
    }
        return 0;
    }

    function getIsFollowerAttribute()
    {
        if(auth()->guard('sanctum')->check()) {
            return is_object(UserFriend::where(['user_id' => auth()->guard('sanctum')->user()->id, 'friend_id' => $this->id])
                ->orWhere(function ($query){
                    $query->where(['friend_id' => auth()->guard('sanctum')->user()->id, 'user_id' => $this->id]);
                })->first())?1:0;
      }
        return 0;
    }
    function blocks(){
        return $this->hasMany(UserBlock::class,'user_id');
    }
    function getLinksAttribute(){
        return Chat::where('is_accepted',1)->where(function ($query){
            $query->where('first_user_id',$this->id)->orWhere('second_user_id',$this->id);
        })->count();
    }
    function getLikesAttribute(){
        $x=0;
        foreach ($this->stories as $one){
            $x +=$one->likes;
        }
        return $x;
    }


}
