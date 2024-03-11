<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Country;

class ProductsApi extends Controller
{
    function index(Request $request){
        $reasons=Product::oldest('title_en')->paginate(20);
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
            'apple_id' => 'sometimes',
            'number'=>'required|numeric',
            'price'=>'required',
            'description'=>'sometimes',
        ]);
    Product::create([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,
            'apple_id'=>$request->apple_id,
            'number'=>$request->number,
            'price'=>$request->price,
            'description'=>$request->description,

        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الأضافه بنجاح'
        ]);
    }
    function update(Product $product,Request $request){
        $request->validate([
            'title_ar' => 'required|unique:products,title_ar,'.$product->id,
            'title_en' => 'required|unique:products,title_en,'.$product->id,
            'apple_id' => 'sometimes',
            'number'=>'required|numeric',
            'price'=>'required',
            'description'=>'sometimes',
        ]);
        $product->update([
            'title_ar'=>$request->title_ar,
            'title_en'=>$request->title_en,
            'apple_id'=>$request->apple_id,
            'number'=>$request->number,
            'price'=>$request->price,
            'description'=>$request->description,
        ]);
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم التعديل بنجاح'
        ]);
    }
    function destroy(Request $request,Product $product){
        $product->delete();
        return response()->json([
            'status'=>true,
            'code'=>200,
            'message'=>'تم الحذف بنجاح'
        ]);
    }
}
