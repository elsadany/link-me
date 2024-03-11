<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\SupscriptionPlan;
use Illuminate\Http\Request;
use App\Models\Country;

class SupscriptionsPlansApi extends Controller
{
    function index(Request $request){
        $reasons=SupscriptionPlan::oldest('name_en')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }
    function store(Request $request)
    {
        $request->validate([

            'name_ar' => 'required|unique:supscription_plans',
            'name_en' => 'required|unique:supscription_plans',
            'days'=>'required|numeric',
            'diamonds'=>'required|numeric',
            'price'=>'required',
            'apple_id'=>'sometimes',
            'title_ar'=>'required|array',
            'title_en'=>'required|array',
            'title_ar.*'=>'required',
            'title_en.*'=>'required',
        ]);
    $plan=SupscriptionPlan::create([
            'name_ar'=>$request->name_ar,
            'name_en'=>$request->name_en,
            'days'=>$request->days,
            'diamonds'=>$request->diamonds,
            'price'=>$request->price,
            'apple_id'=>$request->apple_id,

        ]);
    foreach ($request->title_ar as $k=>$val){
        $plan->features()->create([
            'title_ar'=>$val,
            'title_en'=>$request->title_en[$k]
        ]);
    }
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الأضافه بنجاح'
        ]);
    }
    function update(SupscriptionPlan $supscriptionPlan,Request $request){

        $request->validate([
            'name_ar' => 'required|unique:supscription_plans,name_ar,'.$supscriptionPlan->id,
            'name_en' => 'required|unique:supscription_plans,name_en,'.$supscriptionPlan->id,
            'days'=>'required|numeric',
            'diamonds'=>'required|numeric',
            'price'=>'required',
            'apple_id'=>'sometimes',
            'title_ar'=>'required|array',
            'title_en'=>'required|array',
            'title_ar.*'=>'required',
            'title_en.*'=>'required',
        ]);
        $supscriptionPlan->update([
            'name_ar'=>$request->name_ar,
            'name_en'=>$request->name_en,
            'days'=>$request->days,
            'diamonds'=>$request->diamonds,
            'price'=>$request->price,
            'apple_id'=>$request->apple_id,
        ]);
        $supscriptionPlan->features()->delete();
        foreach ($request->title_ar as $k=>$val) {
            $supscriptionPlan->features()->create([
                'title_ar' => $val,
                'title_en' => $request->title_en[$k]
            ]);
        }
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,SupscriptionPlan $supscriptionPlan){
        $supscriptionPlan->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
