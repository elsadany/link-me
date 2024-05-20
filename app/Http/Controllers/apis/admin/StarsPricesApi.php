<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\StarsPrice;
use Illuminate\Http\Request;
use App\Models\Country;

class StarsPricesApi extends Controller
{
    function index(Request $request){
        $reasons=StarsPrice::oldest('title_en')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }
    function store(Request $request)
    {
        $request->validate([

            'title_ar' => 'required|unique:products',
            'title_en' => 'required|unique:products',
            'diamonds'=>'required|numeric',
            'hours'=>'required|numeric',

        ]);
    StarsPrice::create([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,
            'hours'=>$request->hours,
            'diamonds'=>$request->diamonds,

        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الأضافه بنجاح'
        ]);
    }
    function update(StarsPrice $starPrice,Request $request){
        $request->validate([
            'title_ar' => 'required|unique:products,title_ar,'.$starPrice->id,
            'title_en' => 'required|unique:products,title_en,'.$starPrice->id,
            'diamonds'=>'required|numeric',
            'hours'=>'required|numeric',

        ]);
        $starPrice->update([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,
            'hours'=>$request->hours,
            'diamonds'=>$request->diamonds,
        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,StarsPrice $starPrice){
        $starPrice->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
