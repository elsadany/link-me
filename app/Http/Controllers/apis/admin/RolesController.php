<?php

namespace App\Http\Controllers\apis\admin;

use App\Http\Controllers\Controller;
use App\Models\MovementRequest;
use App\Models\PointsRequestDetail;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users =Role::get();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $users->toArray(),
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
            'permissions'=>'required|array'
        ]);

        $role = Role::create([
            'name'=>$request->name,


        ]);
        foreach ($request->permissions as $one)
        $role->permissions()->create([
            'role'=>$one
        ]);
        $role=Role::with('permissions')->where('id',$role->id)->first();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $role->toArray(),
            'message'=>'تم الأضافة بنجاح'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $role->toArray(),

            'message'=>''
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
                'name' => 'required',
                'permissions'=>'required|array'
        ]

        );

        $role->update([
            'name'=>$request->name,


        ]);
        $role->permissions()->delete();
        foreach ($request->permissions as $one)
            $role->permissions()->create([
                'role'=>$one
            ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $role->toArray(),
            'message'=>'تم التعديل'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => '',
            'message'=>'تم الحذف'
        ]);
    }

}
