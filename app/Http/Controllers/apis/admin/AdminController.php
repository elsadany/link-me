<?php

namespace App\Http\Controllers\apis\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins =Admin::where('id','!=',auth()->guard('admin')->user()->id)->get();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $admins->toArray(),
            'message'=>''
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
                'name' => 'required',
                'email' => 'required|unique:admins',
                'password'=>'required',
            ]
        );
        $admin =Admin::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),

        ]);


        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $admin->toArray(),
            'message'=>'تم الأضافة بنجاح'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $admin->toArray(),
            'message'=>''
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
                'name' => 'required',
                'email' => 'required|unique:admins,email,'.$admin->id,

            ]
        );

        $admin->update($request->except(['password','permissions','confirm_password'])+['updated_by'=>auth()->guard('admin')->user()->id]);
        if($request->has('password')&&$request->password!='')
            $admin->update(['password'=>Hash::make($request->password)]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $admin->toArray(),
            'message'=>'تم التعديل'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {

        $admin->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $admin->toArray(),
            'message'=>'تم الحذف'
        ]);
    }
}
