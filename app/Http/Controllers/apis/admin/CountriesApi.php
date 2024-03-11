<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountriesApi extends Controller
{
    function index(Request $request){
        $countries=Country::oldest('country_enName')->paginate(20);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$countries->toArray()
        ]);
    }
    function show(Request $request){
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>Country::where('id',$request->country_id)->first()->toArray()
        ]);
    }
    function store(Request $request)
    {
        $request->validate([
            'country_code' => 'required|unique:countries',
            'country_enName' => 'required|unique:countries',
            'country_arName' => 'required|unique:countries',
        ]);
    Country::create([
            'country_code'=>$request->country_code,
            'country_enName'=>$request->country_enName,
            'country_arName'=>$request->country_arName,
        'country_enNationality'=>$request->country_enName,
        'country_arNationality'=>$request->country_arName,
        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الأضافه بنجاح'
        ]);
    }
    function update(Country $country,Request $request){
        $request->validate([
            'country_code'=>'required|unique:countries,country_code,'.$country->id,
            'country_enName'=>'required|unique:countries,country_enName,'.$country->id,
            'country_arName'=>'required|unique:countries,country_arName,'.$country->id,
        ]);
        $country->update([
            'country_code'=>$request->country_code,
            'country_enName'=>$request->country_enName,
            'country_arName'=>$request->country_arName,
            'country_enNationality'=>$request->country_enName,
            'country_arNationality'=>$request->country_arName,
        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,Country $country){
        $country->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
