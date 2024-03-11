<?php

namespace App\Http\Controllers\apis\admin;

use App\Models\Admin;
use App\Models\Chat;
use App\Models\Country;
use App\Models\EmailCode;
use App\Models\User;
use App\Models\UsersDiamond;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StudentResource;

class AuthApi extends Controller
{


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

        $user = Admin::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => __('auth.email_not_found'),
                'data' => null

            ]);
        }


        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('personal access token')->plainTextToken;
            $userData = $user->toArray();

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

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $userData,
            'message' => ''
        ]);
    }


    function updatePassword(Request $request)
    {
        $request->validate([
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



        $user->update([
            'name' => $request->name,

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
        $countries = Country::all();
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
        $first_users = Chat::where('first_user_id', $request->user()->id)->pluck('second_user_id')->toArray();
        $second_users = Chat::where('second_user_id', $request->user()->id)->pluck('first_user_id')->toArray();

        $users = User::whereNotIn('id', array_merge($second_users, $first_users, [$request->user()->id]));
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
        } else {
            $first_date = Carbon::now()->subYears(30);
            $users = $users->whereDate('birth_date', '<', $first_date);
        }
        if ($request->country_id != '')
            $users = $users->where('country_id', $request->country_id);
        $users = $users->inRandomOrder()->limit(20)->get();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $users->toArray()
        ]);
    }
    function updateFcmToken(Request $request){
        $user=$request->user()->id;
        $user->fcm_token=$request->fcm_tokon;
        $user->save();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $users->toArray()
        ]);
    }
}
