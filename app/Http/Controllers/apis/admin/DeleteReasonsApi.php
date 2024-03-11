<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use Illuminate\Http\Request;
use App\Models\Country;

class DeleteReasonsApi extends Controller
{
    function index(Request $request){
        $reasons=DeleteReason::oldest('title_en')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray()
        ]);
    }
    function store(Request $request)
    {
        $request->validate([

            'title_ar' => 'required|unique:delete_reasons',
            'title_en' => 'required|unique:delete_reasons',
        ]);
    DeleteReason::create([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,

        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الأضافه بنجاح'
        ]);
    }
    function update(DeleteReason $deleteReason,Request $request){
        $request->validate([
            'title_ar' => 'required|unique:delete_reasons,title_ar,'.$deleteReason->id,
            'title_en' => 'required|unique:delete_reasons,title_en,'.$deleteReason->id,
        ]);
        $deleteReason->update([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,
        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,DeleteReason $deleteReason){
        $deleteReason->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
