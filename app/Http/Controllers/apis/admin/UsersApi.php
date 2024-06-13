<?php

namespace App\Http\Controllers\apis\admin;

use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;

class UsersApi extends Controller
{
    function index(Request $request)
    {
        $reasons = User::where('type', 'user')->with('subscription');
        if ($request->trashed == 1)
            $reasons = $reasons->onlyTrashed();

        if ($request->has('name')) {
            $reasons = $reasons->where('name', 'regexp', $request->name)
                ->orWhere('email', 'regexp', $request->name)
                ->orWhere('user_name', 'regexp', $request->name);
        }
        if ($request->gander != '')
            $reasons = $reasons->where('gander', $request->gander);
        if ($request->has('sort') & $request->sort == 1)
            $reasons = $reasons->oldest('id');
        else
            $reasons = $reasons->latest('id');
        $reasons = $reasons->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $reasons->toArray()
        ]);
    }

    function show(User $user)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $user->toArray()
        ]);
    }
    function update(User $user,Request $request){
        $request->validate([
            'name'=>'required',
            'birth_date'=>'required',

            'email'=>'required|unique:users,email,'.$user->id,
            'user_name'=>'required|unique:users,email,'.$user->id,
            'gander'=>'required|in:male,female',
            'country_id'=>'required|exists:countries,id'
        ]);

        $user->update([
            'name'=>$request->name,
            'birth_date'=>$request->birth_date,
            'user_name'=>$request->user_name,
            'email'=>$request->email,
            'gander'=>$request->gander,
            'country_id'=>$request->country_id,
        ]);
        if($request->password!='')
            $user->update(['password'=>Hash::make($request->password)]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم التعديل بنجاح'
        ]);

    }
    function destroy(Request $request, User $user)
    {
        $user->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم الحذف بنجاح'
        ]);
    }

    function toggleActive(User $user)
    {
        if ($user->is_active == 1)
            $user->update(['is_active' => 0]);
        else
            $user->update(['is_active' => 1]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'تم التعديل بنجاح'
        ]);
    }
    function reports(Request $request){
        $reports=UserReport::with(['user','friend'])->paginate(20);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $reports->toArray()
        ]);
    }
}
