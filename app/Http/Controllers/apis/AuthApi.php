<?php

namespace App\Http\Controllers\apis;

use App\Models\Chat;
use App\Models\Country;
use App\Models\EmailCode;
use App\Models\Notification;
use App\Models\User;
use App\Models\UsersDiamond;
use App\Models\UsersParchase;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StudentResource;
use App\Mail\ActiveEmail;

class AuthApi extends Controller
{
    function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email:filter|unique:users'],
            [
                'email.required' => trans('auth.email_unique'),
                'email.email' => trans('auth.email_invalid'),
                'email.unique' => trans('auth.email_unique'),
            ]);

        $user = User::where('email', $request->email)->first();
        $code = 200;
        if (is_object($user))
            $code = 201;
        EmailCode::where('email', $request->email)->delete();
        $code=EmailCode::create([
            'email' => $request->email,
            'code' => rand(1000, 9999)
        ]);
        Mail::to($request->email)->send(new ActiveEmail($code->code));
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.sent_successfully'),
            'data' => null
        ]);
    }

    function verifyCode(Request $request)
    {
        $request->validate(['email' => 'required|email:filter', 'code' => 'required']
            , [
                'email.required' => __('auth.email_required'),
                'email.email' => __('auth.email_invalid'),
                'code.required' => __('auth.code_required'),
            ]
        );
        $code = EmailCode::where('email', $request->email)->where('code', $request->code)->first();
        if (!is_object($code))
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.code_wrong'),
                'data' => null

            ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'right code',
            'data' => null

        ]);
    }

    function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'birth_date' => 'required|date',
            'email' => 'required|unique:users',
            'password' => 'required',
            'confirm-password' => 'required|same:password',
            'code' => 'required',

        ],
            [
                'email.required' => __('auth.email_required'),
                'email.unique' => __('auth.email_unique'),
                'name.required' => __('auth.name_required'),
                'birth_date.required' => __('auth.birth_date_required'),
                'password.required' => __('auth.password_required'),
                'confirm_password.required' => __('auth.confirm_password_required'),
                'confirm_password.same' => __('auth.confirm_password_same'),
                'email.email' => __('auth.email_invalid'),
                'code.required' => __('auth.code_required'),
            ]);
        $code = EmailCode::where('email', $request->email)->where('code', $request->code)->first();
        if (!is_object($code))
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.code_wrong'),
                'data' => null

            ]);
        $image = '';
        if ($request->has('image'))
            $image = $this->uploadfile($request->file('image'));
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $image,
            'birth_date' => $request->birth_date
        ]);
        $user->update(['user_name' => 'user' . $user->id]);
        $userData = $user->toArray();
        $diamonds = UsersDiamond::where('user_id', $user->id)->where('type', 1)->sum('diamonds');
        $used = UsersDiamond::where('user_id', $user->id)->where('type', 0)->sum('diamonds');
        $userData['diamonds'] = $diamonds - $used;
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.account_created'),
            'data' => $userData,
            'token' => $user->createToken('personal access token')->plainTextToken
        ]);
    }

    function registerVisitor(Request $request)
    {

        $user = User::where('code', $request->mob_code)->first();
        if (!is_object($user)) {
            $user = User::create([
                'name' => 'visitor',
                'email' => 'visitor@visitor.com',
                'password' => Hash::make(Str::random(5)),
                'type' => 'visitor',
                'code'=>$request->mob_code

            ]);
            $user->update(['user_name' => 'user' . $user->id,
                'name' => 'visitor' . $user->id, 'email' => 'visitor' . $user->id . '@linkme.live']);
        }
        $userData = $user->toArray();
        $diamonds = UsersDiamond::where('user_id', $user->id)->where('type', 1)->sum('diamonds');
        $used = UsersDiamond::where('user_id', $user->id)->where('type', 0)->sum('diamonds');
        $userData['diamonds'] = $diamonds - $used;
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.account_created'),
            'data' => $userData,
            'token' => $user->createToken('personal access token')->plainTextToken
        ]);
    }

    function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ],
            [
                'email.required' => __('auth.email_required'),
                'password.required' => __('auth.password_required'),
            ]
        );

        $user = User::where('email', $request->get('email'))->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.email_not_found'),
                'data' => null

            ]);
        }
        if ($user->is_active==0) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'تم أيقاف هذا الأكونت',
                'data' => null

            ]);
        }



        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('personal access token')->plainTextToken;
            $userData = $user->toArray();
            $diamonds = UsersDiamond::where('user_id', $user->id)->where('type', 1)->sum('diamonds');
            $used = UsersDiamond::where('user_id', $user->id)->where('type', 0)->sum('diamonds');
            $userData['diamonds'] = $diamonds - $used;
            $userData['is_subscribed'] = is_object(UsersParchase::latest()->where('user_id', $user->id)->where('finish_at','>=',Carbon::now('Asia/Riyadh'))->first())?1:0;

            return response()->json([
                'status' => true,
                'code' => 200,
                'data' => $userData,
                'token' => $token

            ]);
        } else {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.wrong_password'),
                'data' => null

            ]);
        }

    }

    function myaccount(Request $request)
    {
        $user = $request->user();
        $userData = $user->toArray();
        $diamonds = UsersDiamond::where('user_id', $request->user()->id)->where('type', 1)->sum('diamonds');
        $used = UsersDiamond::where('user_id', $request->user()->id)->where('type', 0)->sum('diamonds');
        $userData['diamonds'] = $diamonds - $used;
        $userData['begin_at'] = null;
        $userData['is_subscribed'] = is_object(UsersParchase::latest()->where('user_id', $user->id)->where('finish_at','>=',Carbon::now('Asia/Riyadh'))->first())?1:0;

        $userData['blocks_number']=$user->blocks()->count();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $userData,
            'message' => ''
        ]);
    }
    function logout(Request $request){
        $user=$request->user();
        $user->update(['fcm_token'=>null]);
        $user->tokens()->delete();

    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email'],
            [
                [
                    'email.required' => __('auth.email_required'),
                    'email.email' => __('auth.email_invalid'),
                ]
            ]);

        $user = User::whereEmail($request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.email_not_found')
            ]);
        }

        $code = EmailCode::whereEmail($request->email)->delete();
        EmailCode::create([
            'email' => $request->email,
            'code' => rand(1000, 9999)sss
        ]);
        Mail::to($request->email)->send(new ActiveEmail($code->code));

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.sent_successfully'),
            'data' => null

        ]);

    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
            'password' => 'required',
            'confirm-password' => 'required|same:password',
        ],
            [
                'email.required' => __('auth.email_required'),
                'password.required' => __('auth.password_required'),
                'confirm_password.required' => __('auth.confirm_password_required'),
                'confirm_password.same' => __('auth.confirm_password_same'),
                'email.email' => __('auth.email_invalid'),
                'code.required' => __('auth.code_required'),
            ]);
        $code = EmailCode::where('email', $request->email)->where('code', $request->code)->first();
        if (!is_object($code))
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.code_wrong'),
                'data' => null

            ]);
        $user = User::where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.email_not_found'),
                'data' => null

            ]);
        }

        $user->password = Hash::make($request->password);


        $user->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.password_reset'),
            'data' => null

        ]);
    }

    function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required',
            'confirm-password' => 'required|same:password',
        ],
            [
                'old_password.required' => __('auth.old_password_required'),
                'password.required' => __('auth.password_required'),
                'confirm_password.required' => __('auth.confirm_password_required'),
                'confirm_password.same' => __('auth.confirm_password_same'),
            ]
        );
        $user = $request->user();
        if (!Hash::check($request->old_password, $user->password)) {

            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.wrong_old_password'),
                'data' => null


            ]);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.password_changed'),
            'data' => $user->toArray()

        ]);
    }

    function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required',
            'birth_date' => 'required|date',

        ], [
            'name.required' => __('auth.name_required'),
            'birth_date.required' => __('auth.birth_date_required'),
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_invalid'),
            'code.required' => __('auth.code_required'),
            'country_id.required' => __('auth.country_required'),
            'country_id.exists' => __('auth.country_exists'),
            'bio.required' => __('auth.bio_required'),
            'gander.required' => __('auth.gander_required'),
            'gander.in' => __('auth.gander_in'),

        ]);
        if ($request->user_name != '')
            $request->validate([
                'user_name' => 'required|string|unique:users,user_name,' . $user->id
            ]);
        $user_name = $user->user_name;
        if ($request->user_name != '')
            $user_name = $request->user_name;
        $image = $user->image;
        if ($request->has('image'))
            $image = $this->uploadfile($request->file('image'));
        $user->update([
            'name' => $request->name,
            'image' => $image,
            'birth_date' => $request->birth_date,
            'country_id' => $request->country_id,
            'bio' => $request->bio,
            'gander' => $request->gander,
            'user_name' => $user_name
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed'),
            'data' => $user->toArray()
        ]);
    }

    private function uploadfile($file)
    {
        $path = 'uploads';
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

    function countries()
    {
        $countries = Country::where('is_active',1)->get();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $countries->toArray()
        ]);
    }

    function completeProfile(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'bio' => 'required',
            'gander' => 'required|in:male,female'
        ], [
            'country_id.required' => __('auth.country_required'),
            'country_id.exists' => __('auth.country_exists'),
            'bio.required' => __('auth.bio_required'),
            'gander.required' => __('auth.gander_required'),
            'gander.in' => __('auth.gander_in'),
        ]);
        $user = $request->user();
        $user->update([
            'country_id' => $request->country_id,
            'bio' => $request->bio,
            'gander' => $request->gander,
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $request->user()->toArray()
        ]);
    }

    function changeEmailSendCode(Request $request)
    {
        $request->validate(['email' => 'required|email:filter|unique:users'],
            [
                'email.required' => trans('auth.email_unique'),
                'email.email' => trans('auth.email_invalid'),
                'email.unique' => trans('auth.email_unique'),
            ]);


        EmailCode::where('email', $request->email)->delete();
        EmailCode::create([
            'email' => $request->email,
            'code' => '5555'
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.sent_successfully')
        ]);
    }

    function confirmChangeEmail(Request $request)
    {
        $request->validate(['email' => 'required|email:filter', 'code' => 'required']
            , [
                'email.required' => __('auth.email_required'),
                'email.email' => __('auth.email_invalid'),
                'code.required' => __('auth.code_required'),
            ]
        );
        $code = EmailCode::where('email', $request->email)->where('code', $request->code)->first();
        if (!is_object($code))
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.code_wrong')
            ]);
        $user = auth()->user();
        $user->update(['email' => $request->email]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed')
        ]);
    }
    function toggleLink(Request $request)
    {
        $user = auth()->user();
        if ($user->is_link == 1)
            $user->is_link = 0;
        else
            $user->is_link = 1;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed')
        ]);
    }
    function toggleAvailabe(Request $request)
    {
        $user = auth()->user();
        if ($user->is_available == 1)
            $user->is_available = 0;
        else
            $user->is_available = 1;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed')
        ]);
    }

    function toggleOnline(Request $request)
    {
        $user = auth()->user();
        if ($user->is_online == 1)
            $user->is_online = 0;
        else
            $user->is_online = 1;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed')
        ]);
    }
    function toggleChats(Request $request)
    {
        $user = auth()->user();
        if ($user->can_see_chats == 1)
            $user->can_see_chats = 0;
        else
            $user->can_see_chats = 1;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.information_changed')
        ]);
    }

    function deleteAccount(Request $request)
    {
        $request->validate([
            'reason' => 'required',
            'password' => 'required'
        ], [
            'reason.required' => __('auth.reason_required'),
            'password.required' => __('auth.password_required'),
        ]);
        $user = $request->user();
        if (!Hash::check($request->password, $user->password)) {

            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.wrong_old_password'),
                'data' => null


            ]);
        }
        $user->update(['reason' => $request->reason]);
        $user->tokens()->delete();
        $user->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('auth.deleted_successfully')
        ]);
    }

    function search(Request $request)
    {
        $request->validate([
            'gander' => 'in:male,female',
            'time_period' => 'in:1,2,3',
            'country_id' => 'exists:countries,id'
        ]);
        $first_users = Chat::where('first_user_id', $request->user()->id)->where('is_accepted',1)->pluck('second_user_id')->toArray();
        $second_users = Chat::where('second_user_id', $request->user()->id)->where('is_accepted',1)->pluck('first_user_id')->toArray();

        $users = User::where('is_active',1)->whereNotIn('id', array_merge($second_users, $first_users, [$request->user()->id]))->where('type','user')->where('id','>',$request->user()->last_user_id);
        if($request->user()->type=='visitor')
            $users=$users->where('is_link',1);
        if ($request->has('gander'))
            $users = $users->where('gander', $request->gander);
        if ($request->time_period == 1) {
            $first_date = Carbon::now()->subYears(16);
            $second_date = Carbon::now()->subYears(24);
            $users = $users->whereDate('birth_date', '<=', $first_date)
                ->whereDate('birth_date', '>=', $second_date);
        } elseif ($request->time_period == 2) {
            $first_date = Carbon::now()->subYears(24);
            $second_date = Carbon::now()->subYears(30);
            $users = $users->whereDate('birth_date', '<', $first_date)
                ->whereDate('birth_date', '>=', $second_date);
        } elseif ($request->time_period == 3) {
            $first_date = Carbon::now()->subYears(30);
            $users = $users->whereDate('birth_date', '<', $first_date);
        }
        if ($request->country_id != '')
            $users = $users->where('country_id', $request->country_id);
        $users = $users->oldest()->limit(5)->where('id','>',auth()->user()->last_user_id)->get();
        $usersarr=$users->toArray();
        $user=$request->user();
        if(count($usersarr)<5)
            $user->update(['last_user_id'=>0]);
        else
        $user->update(['last_user_id'=>$usersarr[count($usersarr)-1]['id']]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' =>$usersarr
        ]);
    }
    function updateFcmToken(Request $request){
        User::where(['fcm_token'=>$request->fcm_token])->update(['fcm_token'=>null]);
        $user=$request->user();
        $user->fcm_token=$request->fcm_tokon;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $user->toArray()
        ]);
    }
    function    showUser(Request $request){
        $user=User::where('id',$request->user_id)->first();
        if(!is_object($user)){
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => __('home.not_found'),
                'data' =>''
            ]);
        }
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' =>'',
                'data' =>$user->toArray()
            ]);

    }
    function notifications(Request $request){
        $notifications=Notification::where('user_id',$request->user()->id)->latest('id')->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' =>'',
            'data' =>$notifications->toArray()
        ]);
    }
    function deleteNotification(Request $request){
        $notifications=Notification::where('user_id',$request->user()->id)->where('id',$request->notification_id)->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' =>'تم حذف الأشعار بنجاح',
            'data' =>null
        ]);
    }
   function readNotification(Request $request){
        $notifications=Notification::where('user_id',$request->user()->id)->where('id',$request->notification_id)->update(['read'=>1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' =>'تم حذف الأشعار بنجاح',
            'data' =>null
        ]);
    }
    function updateShow(Request $request){
        $request->validate([
            'type'=>'required|in:followers,likes,links,chats'
        ]);
            $user=$request->user();
        if($request->type=='followers'){
            $old_status=$user->can_see_followers;
            $new=$old_status==1?0:1;
            $user->update([
                'can_see_followers'=>$new
            ]);
        }
        elseif ($request->type=='links')
        {
            $old_status=$user->can_see_links;
            $new=$old_status==1?0:1;
            $user->update([
                'can_see_links'=>$new
            ]);
        }
        elseif ($request->type=='likes')
        {
            $old_status=$user->can_see_likes;
            $new=$old_status==1?0:1;
            $user->update([
                'can_see_likes'=>$new
            ]);
        }
        elseif ($request->type=='chats')
        {
            $old_status=$user->can_see_chats;
            $new=$old_status==1?0:1;
            $user->update([
                'can_see_chats'=>$new
            ]);
        }
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>null,
            'data'=>$user->toArray()
        ]);
    }
}
