<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\User;
use App\Models\UsersParchase;
use Illuminate\Http\Request;
use App\Models\Country;

class PurchasesApi extends Controller
{
    function index(Request $request){
        $reasons=UsersParchase::with(['plan','user'])->latest('id')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }


}
