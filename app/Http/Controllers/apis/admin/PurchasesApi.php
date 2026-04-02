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
use Illuminate\Support\Facades\Cache;

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

           $data = Cache::remember('admin_purchases_totals_v1', 60, function () {
               $now = Carbon::now();

               return [
                   'day_total' => UsersParchase::whereDate('paid_at', $now)->sum('paid'),
                   'week_total' => UsersParchase::whereDate('paid_at', '<=', $now)->whereDate('paid_at', '>=', $now->copy()->subWeek())->sum('paid'),
                   'month_total' => UsersParchase::whereDate('paid_at', '<=', $now)->whereDate('paid_at', '>=', $now->copy()->subMonth())->sum('paid'),
                   'total' => UsersParchase::sum('paid'),
               ];
           });
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
        $data = Cache::remember('admin_diamonds_totals_v1', 60, function () {
            $now = Carbon::now();

            return [
                'day_total' => UsersDiamond::whereNotNull('product_id')->whereDate('paid_at', $now)->sum('amount'),
                'week_total' => UsersDiamond::whereNotNull('product_id')->whereDate('paid_at', '<=', $now)->whereDate('paid_at', '>=', $now->copy()->subWeek())->sum('amount'),
                'month_total' => UsersDiamond::whereNotNull('product_id')->whereDate('paid_at', '<=', $now)->whereDate('paid_at', '>=', $now->copy()->subMonth())->sum('amount'),
                'total' => UsersDiamond::whereNotNull('product_id')->sum('amount'),
            ];
        });
        return response()->json([
            'status'=>true,
            'code'=>200,
            'data'=>$reasons->toArray(),
            'other_data'=>$data
        ]);
    }


}
