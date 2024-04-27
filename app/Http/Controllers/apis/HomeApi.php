<?php

namespace App\Http\Controllers\apis;

use App\Models\AppSetting;
use App\Models\Contact;
use App\Models\Country;
use App\Models\DeleteReason;
use App\Models\EmailCode;
use App\Models\StoriesComment;
use App\Models\StoriesLike;
use App\Models\StoriesReport;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFriend;
use App\Models\UserReport;
use App\Models\UsersStory;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StudentResource;
use function PHPUnit\Framework\lessThanOrEqual;

class HomeApi extends Controller
{
    function appSetting(Request $request)
    {
        $data = [];
        $setting = AppSetting::first();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $setting->toArray()
        ]);
    }

    function storeTicket(Request $request)
    {
        $request->validate([
            'email' => 'required|email:filter',
            'title' => 'required',
            'description' => 'required',
            'type' => 'required|in:suggestion,complain'
        ]);
        Ticket::create([
            'email' => $request->email,
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'user_id' => $request->user()->id
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.ticket_submitted')
        ]);
    }

    function mytickets(Request $request)
    {
        $tickets = Ticket::where('user_id', $request->user()->id)->latest('id');
        if ($request->status != '')
            $tickets = $tickets->where('status', $request->status);
        if ($request->type != '')
            $tickets = $tickets->where('type', $request->type);
        if ($request->ticket_type) {
            if ($request->ticket_type == 'box')
                $tickets = $tickets->where('is_read', 0);
            else
                $tickets = $tickets->where('is_read', 1);

        }
        $tickets = $tickets->paginate(20);
        Ticket::where('user_id', $request->user()->id)->latest('id')->update(['is_read' => 1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'dats' => $tickets->toArray()
        ]);
    }

    function postReply(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'reply' => 'required'
        ]);
        $request->user()->ticketReplies()->create([
            'ticket_id' => $request->ticket_id,
            'reply' => $request->reply,
            'status' => ''
        ]);
        Ticket::where('id', $request->ticket_id)->first()->update(['status' => 'user_reply', 'is_read' => 1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.ticket_reply_submitted')
        ]);
    }

    function addStory(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,ogg,qt,jpg,jpeg,png,bmp,tiff|max:6120 ',
            'text' => 'sometimes'
        ]);
        $user = $request->user();
        $user->stories()->create([
            'file' => $this->uploadfile($request->file('video')),
            'expire_at' => Carbon::now('Asia/Riyadh')->addDay(),
            'text' => $request->text
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.story_submitted')
        ]);
    }

    function getStories(Request $request)
    {
        $stories = User::latest('id')->whereHas('stories')->with('stories')->paginate(12);
        $user_ids = UserFriend::where('user_id', $request->user()->id)->pluck('friend_id')->toArray();
        $user_ids = array_merge($user_ids, UserFriend::where('user_id', $request->user()->id)->pluck('friend_id')->toArray());
        $posts = User::latest('id')->whereIn("id", $user_ids)->where('id', '!=', $request->user()->id)->whereHas('stories')->with('stories')->paginate(6);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $posts->toArray(),
            'post' => $stories->toArray()
        ]);
    }

    private function uploadfile($file)
    {
        $path = 'uploads/stories';
        if (!file_exists($path)) {
            mkdir($path, 0775);
        }
        $datepath = date('m-Y', strtotime(\Carbon\Carbon::now()));
        if (!file_exists($path . '/' . $datepath)) {
            mkdir($path . '/' . $datepath, 0775);
        }
        $newdir = $path . '/' . $datepath;
        $exten = $file->getClientOriginalExtension();
        $filename = Str::random(10);
        $filename = $filename . '.' . $exten;
        $file->move($newdir, $filename);
        return $newdir . '/' . $filename;
    }

    function storeContact(Request $request)
    {
        $request->validate([
            'email' => 'required|email:filter',
            'title' => 'required',
            'description' => 'required',
            'type' => 'required|in:suggestion,complain'
        ]);
        Contact::create([
            'email' => $request->email,
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.ticket_submitted')
        ]);
    }

    function reasons(Request $request)
    {
        $reasons = DeleteReason::get();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $reasons->toArray()
        ]);
    }

    function addToggleLike(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:users_stories,id'
        ]);
        $like = StoriesLike::where('story_id', $request->story_id)->where('user_id', $request->user()->id)->first();
        if (is_object($like))
            $like->delete();
        else
            StoriesLike::create([
                'story_id' => $request->story_id,
                'user_id' => $request->user()->id
            ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function addComment(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:users_stories,id',
            'comment' => 'required'
        ]);

        StoriesComment::create([
            'story_id' => $request->story_id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function deleteComment(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:stories_comments,id'
        ]);
        StoriesComment::where('id', $request->comment_id)->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function deleteStory(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:users_stories,id'
        ]);
        UsersStory::where('id', $request->story_id)->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function reportStory(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:users_stories,id'
        ]);
        StoriesReport::firstOrCreate([
            'user_id' => $request->user()->id,
            'story_id' => $request->story_id
        ], ['reason' => $request->reason]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        UserBlock::firstOrCreate([
            'user_id' => $request->user()->id,
            'friend_id' => $request->user_id
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success'
        ]);
    }

    function blockedUsers(Request $request)
    {

        $ids = UserBlock::where('user_id', $request->user()->id)->pluck('friend_id')->toArray();
        $users = User::whereIn('id', $ids)->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success',
            'data' => $users->toArray()
        ]);
    }

    function deleteBlock(Request $request)
    {
        $request->validate(['user_id' => 'required']);
        UserBlock::where('user_id', $request->user()->id)->where('friend_id', $request->user_id)->delete();
        $ids = UserBlock::where('user_id', $request->user()->id)->pluck('friend_id')->toArray();
        $users = User::whereIn('id', $ids)->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'success',
            'data' => $users->toArray()
        ]);
    }

    function getFriends(Request $request)
    {
        $user_friends = UserFriend::where('user_id', $request->user()->id)
            ->orWhere('friend_id', $request->user()->id)->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $user_friends->toArray()
        ]);

    }

    function deleteFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required'
        ]);
        UserFriend::where(['user_id' => $request->user()->id, 'friend_id' => $request->friend_id])
            ->where(['user_id' => $request->friend_id, 'friend_id' => $request->user()->id])->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح'
        ]);
    }

    function reportUser(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
            'reason' => 'sometimes'
        ]);
        UserReport::firstOrCreate([
            'user_id' => $request->user()->id,
            'friend_id' => $request->friend_id
        ], ['reason' => $request->reason]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الأرسال بنجاح'
        ]);
    }

    function unFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);
        UserFriend::where([
            'user_id' => $request->user()->id,
            'friend_id' => $request->friend_id
        ])->orWhere([
            'friend_id' => $request->user()->id,
            'user_id' => $request->friend_id
        ])->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح'
        ]);
    }
}
