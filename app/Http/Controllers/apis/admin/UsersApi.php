<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Country;

class UsersApi extends Controller
{
    function index(Request $request){
        $reasons=User::where('type','user');
        if($request->trashed==1)
            $reasons=$reasons->onlyTrashed();

        if($request->has('name')){
            $reasons=$reasons->where('name','regexp',$request->name)
                ->orWhere('email','regexp',$request->name)
                ->orWhere('user_name','regexp',$request->name);
        }
        if($request->gander!='')
            $reasons=$reasons->where('gander',$request->gander);
        if($request->has('sort')&$request->sort==1)
            $reasons=$reasons->oldest('id');
        else
            $reasons=$reasons->latest('id');
            $reasons=$reasons->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }
function show(User $user){
    return response()->json([
        'status'=>true,
            'code'=>200,
            'data'=>$user->toArray()
        ]);
}
    function destroy(Request $request,User $user){
        $user->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
    function toggleActive(User $user){
        if($user->is_active==1)
            $user->update(['is_active'=>0]);
        else
            $user->update(['is_active'=>1]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
}
