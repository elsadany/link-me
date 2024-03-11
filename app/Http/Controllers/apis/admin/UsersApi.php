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
        $reasons=User::where('type','user')->latest('id')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
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
}
