<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\User;
use App\Models\UsersStory;
use Illuminate\Http\Request;
use App\Models\Country;

class UsersStoriesApi extends Controller
{
    function index(Request $request){
        $reasons=UsersStory::with('user')->latest('id')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }

    function destroy(Request $request,UsersStory $usersStory){
        $usersStory->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
