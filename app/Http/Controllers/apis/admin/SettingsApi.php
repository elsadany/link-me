<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\Country;

class SettingsApi extends Controller
{
    function index(Request $request){
    $settings=AppSetting::first();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$settings->toArray()
        ]);
    }
    function show(Request $request,Ticket $ticket){
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>Ticket::where('id',$ticket->id)->with('replies')->first()->toArray()
        ]);
    }

    function update(AppSetting $setting,Request $request){
        $request->validate([
            'about_ar'=>'required',
            'about_en'=>'required',
            'privacy_en'=>'required',
            'privacy_ar'=>'required',
            'terms_en'=>'required',
            'terms_ar'=>'required',
            'about_star_ar'=>'required',
            'about_star_en'=>'required',

        ]);
       $user=$request->user();
       $setting->update([
           'about_ar'=>$request->about_ar,
           'about_en'=>$request->about_en,
           'privacy_en'=>$request->privacy_en,
           'privacy_ar'=>$request->privacy_ar,
           'terms_en'=>$request->terms_en,
           'terms_ar'=>$request->terms_ar,
           'about_star_ar'=>$request->about_star_ar,
           'about_star_en'=>$request->about_star_en,
       ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,Ticket $ticket){
        $ticket->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
    function toggleActive(Country $country){
        if($country->is_active==1)
            $country->update(['is_active'=>0]);
        else
            $country->update(['is_active'=>1]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
}
