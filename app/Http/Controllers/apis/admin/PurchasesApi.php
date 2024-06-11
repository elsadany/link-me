<?php
namespace App\Http\Controllers\apis\admin;
use App\Http\Controllers\Controller;
use App\Models\DeleteReason;
use App\Models\Product;
use App\Models\User;
use App\Models\UsersDiamond;
use App\Models\UsersParchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Country;

class PurchasesApi extends Controller
{
    function index(Request $request){
        $reasons=UsersParchase::with(['plan','user'])->latest('id');
        if($request->get('date_from')!=''){
        $reasons=$reasons->whereDate('paid_at','>=',$request->date_from);
        }
        if($request->get('date_to')!=''){
            $reasons=$reasons->whereDate('paid_at','<=',$request->date_to);
        }
        if($request->get('name')!=''){
        $users_ids=User::where('name','REGEXP',$request->name)->pluck('id')->toArray();
        $reasons=$reasons->whereIn('user_id',$users_ids);
        }
        if($request->plan_id!='')
            $reasons=$reasons->where('subscription_plan_id',$request->plan_id);
           $reasons=$reasons->paginate(20);

           $data['day_total']=UsersParchase::whereDate('paid_at',Carbon::now())->sum('paid');
           $data['week_total']=UsersParchase::whereDate('paid_at','<=',Carbon::now())->whereDate('paid_at','>=',Carbon::now()->subWeek())->sum('paid');
           $data['month_total']=UsersParchase::whereDate('paid_at','<=',Carbon::now())->whereDate('paid_at','>=',Carbon::now()->subMonth())->sum('paid');
           $data['total']=UsersParchase::sum('paid');
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray(),
            'other_data'=>$data
        ]);
    }
    function diamonds(Request $request){
        $reasons=UsersDiamond::whereNotNull('product_id')->with(['product','user'])->latest('id');
        if($request->get('date_from')!=''){
            $reasons=$reasons->whereDate('paid_at','>=',$request->date_from);
        }
        if($request->get('date_to')!=''){
            $reasons=$reasons->whereDate('paid_at','<=',$request->date_to);
        }
        if($request->get('name')!=''){
            $users_ids=User::where('name','REGEXP',$request->name)->pluck('id')->toArray();
            $reasons=$reasons->whereIn('user_id',$users_ids);
        }
        if($request->product_id!='')
            $reasons=$reasons->where('product_id',$request->product_id);

        $reasons=$reasons->paginate(20);
        $data['day_total']=UsersDiamond::whereNotNull('product_id')->whereDate('paid_at',Carbon::now())->sum('total');
        $data['week_total']=UsersDiamond::whereNotNull('product_id')->whereDate('paid_at','<=',Carbon::now())->whereDate('paid_at','>=',Carbon::now()->subWeek())->sum('total');
        $data['month_total']=UsersDiamond::whereNotNull('product_id')->whereDate('paid_at','<=',Carbon::now())->whereDate('paid_at','>=',Carbon::now()->subMonth())->sum('total');
        $data['total']=UsersDiamond::whereNotNull('product_id')->sum('total');
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray(),
            'other_data'=>$data
        ]);
    }


}
