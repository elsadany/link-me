<?php

namespace App\Http\Controllers\apis;

use App\Events\LinkRequest;
use App\Events\SendFcmNotificationEvent;
use App\Models\AppSetting;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Contact;
use App\Models\Country;
use App\Models\DeleteReason;
use App\Models\EmailCode;
use App\Models\MessageReport;
use App\Models\StoriesComment;
use App\Models\StoriesLike;
use App\Models\StoriesReport;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFriend;
use App\Models\UsersStory;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StudentResource;
use function PHPUnit\Framework\lessThanOrEqual;

class ChatsApi extends Controller
{
    function sendChatRequest(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'sometimes',
            'is_special' => 'required|boolean'
        ]);
        if ($request->user()->id == $request->user_id)
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'لا يمكن ارسال طلب لنفسك '
            ]);
        $user = $request->user();
        $second_user = User::findOrFail($request->user_id);
        if (($request->user()->type == 'visitor' || $second_user->type == 'visitor') && $request->type == 'friend_request') {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'لا يمكنك ارسال طلب صداقه '
            ]);
        }

        if ($request->user()->type == 'visitor' && $user->number_of_request > 4) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'لقد استزفت عدد الطلبات الخاص بك '
            ]);
        }      if ($request->user()->type == 'visitor' && $second_user->is_link == 0) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'لا يمكن ارسال طلب لهذا المستخدم '
            ]);
        }

        $chat = Chat::where(['first_user_id' => auth()->user()->id, 'second_user_id' => $request->user_id])
            ->orWhere(function ($query) use ($request) {
                $query->where(['second_user_id' => $request->user()->id, 'first_user_id' => $request->user_id]);
            })->first();
        $x = 0;
        $chat_request = 1;
        if (is_object($chat) && $chat->type == 'home')
            $chat_request = 0;
        if (!is_object($chat))
            $chat = Chat::create([
                'first_user_id' => auth()->user()->id,
                'second_user_id' => $request->user_id,
                'is_special' => $request->is_special,
                'type' => $request->type,
                'delete_from_first_user' => 0, 'delete_from_second_user' => 0,
                'is_ended' => 0

            ]);
        else {
            $chat->update(['first_user_id' => auth()->user()->id,
                'second_user_id' => $request->user_id, 'type' => $request->type, 'is_accepted' => 0, 'delete_from_first_user' => 0, 'delete_from_second_user' => 0]);
            $x = 1;
        }
        $chat = Chat::find($chat->id);
        event(new LinkRequest(
            $chat->id,
            $chat->is_accepted
        ));
        if ($request->type != 'home') {


            event(new SendFcmNotificationEvent([$second_user->fcm_token], 'تم ارسال طلب اليك', 'تم ارسال طلب اليك من' . $request->user()->name, ['chat_id' => $chat->id, 'sender_id' => $request->user()->id, 'is_accepted' => $chat->is_accepted, 'type' => $request->type]));
            if ($chat_request == 1)
                $chat->update(['expire_at' => strtotime(Carbon::now('Asia/Riyadh')->addMinutes(10)) * 1000, 'is_sent' => 1]);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $chat->toArray()
        ]);
    }

    function accept(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:chats,id',

        ]);
        $chat = Chat::find($request->request_id);
        $chat->is_accepted = 1;
        $chat->save();
        if ($chat->type == 'friend_request') {
            $user_friend = UserFriend::where(['user_id' => $chat->first_user_id, 'friend_id' => $chat->second_user_id])
                ->orWhere(function ($q) use ($chat) {
                    $q->where(['user_id' => $chat->second_user_id, 'friend_id' => $chat->first_user_id]);

                })->first();
            if (!is_object($user_friend)) {
                UserFriend::create([
                    'user_id' => $chat->first_user_id, 'friend_id' => $chat->second_user_id
                ]);
            }
        }

        event(new LinkRequest(
            $chat->id,
            $chat->is_accepted
        ));

        if (in_array($chat->type, [ 'friend_request','home']))
            $chat->update(['expire_at' => null]);
        if ($chat->type == 'friend_request')
            event(new SendFcmNotificationEvent([$chat->firstUser->fcm_token], 'تم الموافقه على الطلب الخاص بك', 'تم الموافقه على الطلب الخاص بك من طرف' . $chat->secondUser->name, ['chat_id' => $chat->id, 'sender_id' => $request->user()->id, 'is_accepted' => $chat->is_accepted, 'type' => 'chat_accepted'], 'acceptOrReject'));
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $chat->toArray()
        ]);

    }

    function refuse(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:chats,id',

        ]);
        $chat = Chat::find($request->request_id);
        $chat->is_accepted = 2;
        $chat->save();
        event(new LinkRequest(
            $chat->id,
            $chat->is_accepted
        ));
//        if ($chat->type == 'friend_request')
            event(new SendFcmNotificationEvent([$chat->firstUser->fcm_token], 'تم رفض الطلب الخاص بك', ' تم رفض الطلب الخاص بك من طرف' . $chat->secondUser->name, ['chat_id' => $chat->id, 'sender_id' => $request->user()->id, 'is_accepted' => $chat->is_accepted, 'type' => 'chat_rejected'], 'acceptOrReject'));

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $chat->toArray()
        ]);

    }

    function chats(Request $request)
    {
        $chats = Chat::where(function ($q) use ($request) {
            $q->where(['first_user_id' => auth()->user()->id, 'delete_from_first_user' => 0])
                ->orWhere(function ($query) use ($request) {
                    $query->where(['second_user_id' => $request->user()->id, 'delete_from_second_user' => 0]);
                });
        });
        if ($request->name != '') {
            $ids = User::where('name', 'like', '%' . $request->name . '%')->pluck('id')->toArray();
            $chats = $chats->where(function ($query) use ($request, $ids) {
                $query->whereIn('first_user_id', $ids)->orWhereIn('second_user_id', $ids);
            });

        }
        $chats = $chats->latest('updated_at')->paginate(10);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $chats->toArray()
        ]);
    }

    function oneChat(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id'
        ]);
        $chats = Chat::findOrFail($request->chat_id);
        if($request->type=='home')
            $chats->update(['type'=>'home','expire_at'=>null]);

        $messages = $chats->messages();
        if ($chats->first_user_id == $request->user()->id)
            $messages = $messages->where('delete_from_first_user', 0);
        elseif ($chats->second_user_id == $request->user()->id)
            $messages = $messages->where('delete_from_second_user', 0);
        $messages = $messages->paginate(20);
        $chats->messages()->where('sender_id', '!=', $request->user()->id)->update(['read' => 1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $messages->toArray(),
            'chat' => $chats->toArray()
        ]);
    }

    function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'type' => 'required|in:text,file',
            'message' => 'required_if:type,==,text',
            'media' => 'required_if:type,==,file|file',
            'media_type' => 'required_if:type,==,file',
            'one_time' => 'boolean'
        ]);
        if ($request->type == 'file' && $request->media_type == 'image')
            $request->validate(['media' => 'max:5120']);
        elseif ($request->type == 'file' && $request->media_type == 'video')
            $request->validate(['media' => 'max:30720']);
        $chat = Chat::find($request->chat_id);
        $reciever = User::find($chat->first_user_id);
        if($chat->is_counted==0){
            $reciever->update(['number_of_request' => $reciever->number_of_request + 1]);
            $chat->update(['is_counted'=>1]);
        }
        if ($chat->first_user_id == $request->user()->id)
            $reciever = User::find($chat->second_user_id);
        $ids = UserBlock::where('user_id', $request->user()->id)->pluck('friend_id')->toArray();
        $ids = array_merge($ids,UserBlock::where('friend_id', $request->user()->id)->pluck('user_id')->toArray());
        if (in_array($reciever->id, $ids)||in_array($request->user()->id,$ids))
            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $chat->toArray()
            ]);

        $chat->updated_at = Carbon::now('Asia/Riyadh');
        $chat->save();
        $message = $chat->messages()->create([
            'message' => $request->message,
            'sender_id' => $request->user()->id,
            'type' => $request->type,
            'media_type' => $request->media_type,
            'one_time' => $request->one_time == 1 ? 1 : 0,
            'media_name' => $request->hasFile('media') ? $this->uploadfile($request->file('media')) : null
        ]);
        event(new \App\Events\Chat(
            $chat->id,
            $request->user()->id,
            $reciever->id,
            $request->user()->name,
            $request->user()->imagePath,
            $request->message,
            $request->type,
            $request->media_type,
            $message->filePath,
            $message->created_at,
            $message->one_time

        ));

        $chat = Chat::find($chat->id);
        event(new SendFcmNotificationEvent([$reciever->fcm_token], 'تم ارسال رسالة لك', 'تم ارسال رسالة لك',
            ['chat_id' => $chat->id, 'sender_id' => $request->user()->id, 'reciever_id' => $reciever->id, 'message' => $request->message, 'is_accepted' => $chat->is_accepted, 'type' => 'chat',
                'chat_message_type' => $request->type, 'media_type' => $request->media_type, 'file' => $message->filePath, 'one_time' => $message->one_time]));

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $chat->toArray()
        ]);
    }

    private function uploadfile($file)
    {
        $path = 'uploads';
        if (!file_exists($path)) {
            mkdir($path, 0775);
        }
        $path = 'uploads/chats';
        if (!file_exists($path)) {
            mkdir($path, 0775);
        }
        $datepath = date('m-Y', strtotime(\Carbon\Carbon::now()));
        if (!file_exists($path . '/' . $datepath)) {
            mkdir($path . '/' . $datepath, 0775);
        }
        $newdir = $path . '/' . $datepath;
        $exten = $file->getClientOriginalExtension();
        $filename = Str::random(15);
        $filename = $filename . '.' . $exten;
        $file->move($newdir, $filename);
        return $newdir . '/' . $filename;
    }

    function hideMessage(Request $request)
    {
        $request->validate(['message_id' => 'required|exists:chat_messages,id']);
        $chat_message = ChatMessage::find($request->message_id);
        $chat = Chat::find($chat_message->chat_id);
        if ($request->user()->id == $chat->first_user_id)
            $chat_message->update(['delete_from_first_user' => 1]);
        elseif ($request->user()->id == $chat->second_user_id)
            $chat_message->update(['delete_from_second_user' => 1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح',
            'data' => null
        ]);
    }

    function deleteMessage(Request $request)
    {
        $request->validate(['message_id' => 'required|exists:chat_messages,id']);
        $chat_message = ChatMessage::where('id', $request->message_id)->where('sender_id', $request->user()->id)->first();
        if (!is_object($chat_message)) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'انت لا تمتلك هذة الرسالة',
                'data' => null
            ]);
        }
        $chat_message->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح',
            'data' => null
        ]);
    }

    function EndChat(Chat $chat)
    {
        $chat->delete();
        $chat->messages()->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح',
            'data' => null
        ]);
    }

    function deleteChat(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|array'
        ]);

        $chats = Chat::whereIn('id', $request->chat_id)->get();
        foreach ($chats as $chat) {
            if ($request->user()->id == $chat->first_user_id) {
                $chat->update(['delete_from_first_user' => 1]);
                $chat->messages()->delete(['delete_from_first_user' => 1]);

            } elseif ($request->user()->id == $chat->second_user_id) {
                $chat->update(['delete_from_second_user' => 1]);
                $chat->messages()->delete(['delete_from_second_user' => 1]);

            }
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح',
            'data' => null
        ]);
    }

    function boomarkMassege(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chat_messages,id'
        ]);
        $chat_message = ChatMessage::find($request->message_id);
        $chat = Chat::find($chat_message->chat_id);
        if ($request->user()->id == $chat->first_user_id) {
            $chat_message->update(['bookmark_from_first_user' => 1]);
        } elseif ($request->user()->id == $chat->second_user_id) {
            $chat_message->update(['bookmark_from_second_user' => 1]);

        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحفظ بنجاح',
            'data' => null
        ]);
    }

    function reportMassege(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chat_messages,id',
            'reason' => 'sometimes'
        ]);
        MessageReport::firstOrCreate([
            'user_id' => $request->user()->id,
            'message_id' => $request->message_id
        ], ['reason' => $request->reason]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحفظ بنجاح',
            'data' => null
        ]);
    }

    function star()
    {

    }
}
