<?php

namespace App\Http\Controllers\apis;

use App\Models\Chat;
use App\Models\Product;
use App\Models\StarsPrice;
use App\Models\SupscriptionPlan;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFriend;
use App\Models\UsersDiamond;
use App\Models\UsersParchase;
use App\Models\UserStar;
use App\Models\WaitingUserStar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\lessThanOrEqual;

class SupscriptionApi extends Controller
{
    function plans(Request $request){
        $last_supscription=UsersParchase::latest('id')->where('user_id',$request->user()->id)->first();

        $last_supscription_data=['is_subscribed'=>0,"is_finished"=>0,'finish_at'=>null];
        if(is_object($last_supscription)) {
            $last_supscription = UsersParchase::latest()->where('user_id',$request->user()->id)->whereDate('finish_at','>=',Carbon::now('Asia/Riyadh'))->first();
            if(is_object($last_supscription))
                $last_supscription_data=['is_subscribed'=>1,"is_finished"=>0,'finish_at'=>$last_supscription->finish_at];
            else
                $last_supscription_data=['is_subscribed'=>1,"is_finished"=>1,'finish_at'=>null];
        }
        $supscription=null;
        if(is_object($last_supscription))
            $supscription=UsersParchase::where('id',$last_supscription->id)->first()->toArray();
        $diamonds=UsersDiamond::where('user_id',$request->user()->id)->where('type',1)->sum('diamonds');
        $used=UsersDiamond::where('user_id',$request->user()->id)->where('type',0)->sum('diamonds');
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => ['plans'=>SupscriptionPlan::get()->toArray(),
                'products'=>Product::get()->toArray(),
                'stars'=>StarsPrice::get()->toArray(),
                'diamonds'=>$diamonds-$used,
                'last_supscription_data'=>$last_supscription_data,
                'last_supscription'=>$supscription,
            ]
        ]);
    }
    function products(Request $request){
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => Product::get()->toArray()
        ]);
    }
    function stars(Request $request){
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => StarsPrice::get()->toArray()
        ]);
    }
    function getLastSupscription(Request $request){
        $last_supscription=UsersParchase::where('user_id',$request->user()->id)->first();
        $supscription=[];
        if(is_object($last_supscription))
            $supscription=SupscriptionPlan::where('id',$last_supscription->subscription_plan_id)->first()->toArray();
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $supscription
        ]);
    }
    function getSupscriptionStatus(Request $request){
        $last_supscription=UsersParchase::latest()->where('user_id',$request->user()->id)->first();

        if(is_object($last_supscription)) {
            $supscription = SupscriptionPlan::latest()->where('id', $last_supscription->subscription_plan_id)->whereDate('finish_at','>=',Carbon::now('Asia/Riyadh'))->first();
            if(is_object($supscription))
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => 'subscribed',
                    'data'=>['is_subscribed'=>1,"is_finished"=>0,'finish_at'=>$supscription->finish_at]

                ]);
            else
                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => 'finished',
                    'data'=>['is_subscribed'=>1,"is_finished"=>1,'finish_at'=>null]

                ]);
        }else{
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'finished',
                'data'=>['is_subscribed'=>0,"is_finished"=>0,'finish_at'=>null]

            ]);
        }
    }

    function buySubscription(Request $request){
        $request->validate([
            'supscription_plan_id'=>'required|exists:supscription_plans,id',
            'transction_id'=>'required',
            'paid_by'=>'required|in:android,ios'

        ]);
        $supscription_plan=SupscriptionPlan::find($request->supscription_plan_id);
        $last_supscription=UsersParchase::where('user_id',$request->user()->id)->whereDate('finish_at','>=',Carbon::now('Asia/Riyadh'))->first();
        $expire=Carbon::now('Asia/Riyadh')->addDays($supscription_plan->days);
        if(is_object($last_supscription))
            $expire= Carbon::createFromFormat('Y-m-d H:i:s', $last_supscription->finish_at)->addDays($supscription_plan->days);
        $parchase=UsersParchase::create([
            'paid_at'=>Carbon::now('Asia/Riyadh'),
            'finish_at'=>$expire,
            'subscription_plan_id'=>$supscription_plan->id,
            'paid'=>$supscription_plan->price,
            'user_id'=>$request->user()->id,
            'transaction_id'=>$request->transaction_id,
            'paid_by'=>$request->paid_by
        ]);
        $diamonds=UsersDiamond::create([
            'user_id'=>$request->user()->id,
            'diamonds'=>$supscription_plan->diamonds,
            'type'=>1,
            'paid_at'=>null,
            'product_id'=>null
        ]);
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.subscribed'),


        ]);
    }
    function getDiamonds(Request $request){
        $diamonds=UsersDiamond::where('user_id',$request->user()->id)->where('type',1)->sum('diamonds');
        $used=UsersDiamond::where('user_id',$request->user()->id)->where('type',0)->sum('diamonds');
        return response()->json([
            'status' => true,
            'code' => 200,
            'data'=>$diamonds-$used


        ]);

    }
    function buyDiamonds(Request $request){
        $request->validate([
            'product_id'=>'required|exists:products,id',
            'transaction_id'=>'required',
            'paid_by'=>'required|in:android,ios'

        ]);
        $product=Product::find($request->product_id);
        $userDiamonds=UsersDiamond::create([
            'user_id'=>$request->user()->id,
            'product_id'=>$request->product_id,
            'paid_by'=>$request->paid_by,
            'paid_at'=>Carbon::now('Asia/Riyadh'),
            'type'=>1,
            'diamonds'=>$product->number,
            'amount'=>$product->price

        ]);
        $diamonds=UsersDiamond::where('user_id',$request->user()->id)->where('type',1)->sum('diamonds');
        $used=UsersDiamond::where('user_id',$request->user()->id)->where('type',0)->sum('diamonds');
        return response()->json([
            'status' => true,
            'code' => 200,
            'data'=>$diamonds-$used


        ]);
    }
    function buyStar(Request $request){
        $request->validate([
            'star_price_id'=>'required|exists:stars_prices,id',

        ]);
        $starPrice=StarsPrice::find($request->star_price_id);
        $diamonds=UsersDiamond::where('user_id',$request->user()->id)->where('type',1)->sum('diamonds');
        $used=UsersDiamond::where('user_id',$request->user()->id)->where('type',0)->sum('diamonds');
        $user_diamonds=$diamonds-$used;
        if($user_diamonds<$starPrice->diamonds){
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'you don`t have enough diamonds',
                'errors'=>new \stdClass('you don`t have enough diamonds')

            ]);
        }
        $users_count=User::join('user_stars','user_stars.user_id','=','users.id')
            ->where('user_stars.expired_at','>',Carbon::now('Asia/Riyadh'))
            ->latest('user_stars.expired_at')
            ->select('users.*')
            ->count();
        if($users_count<=50) {
            UsersDiamond::create([
                'diamonds' => $starPrice->diamonds,
                'user_id' => $request->user()->id,
                'type' => 0,

            ]);
            UserStar::create([
                'user_id' => $request->user()->id,
                'expired_at' => Carbon::now('Asia/Riyadh')->addHours($starPrice->hours),
                'star_price_id' => $request->star_price_id
            ]);
        }
        else {
            $last=UserStar::oldest('expire_at')->first();
            UsersDiamond::create([
                'diamonds' => $starPrice->diamonds,
                'user_id' => $request->user()->id,
                'type' => 0,

            ]);
            WaitingUserStar::create([
                'user_id' => $request->user()->id,
                'expired_at' => $last->expire_at->addHours($starPrice->hours),
                'begin_at' => $last->expire_at,
                'star_price_id' => $request->star_price_id
            ]);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.subscribed'),


        ]);
    }
    function topUsers(Request $request){
        $users_ids=UserBlock::where('user_id',$request->user()->id)->pluck('friend_id')->toArray();
        $friends_ids=UserBlock::where('friend_id',$request->user()->id)->pluck('user_id')->toArray();
        $users_ids=array_merge($users_ids,$friends_ids);
        $users=Cache::remember('paid_top','10*60*60',function ()use($users_ids) {
           return User::where('is_active', 1)->join('user_stars', 'user_stars.user_id', 'users.id')
                ->where('user_stars.expired_at', '>=', Carbon::now('Asia/Riyadh')->format('Y-m-d H:m:s'))
                ->latest('user_stars.expired_at')
                ->whereNotIn('users.id', $users_ids)
                ->select('users.*')
                ->get();
        });
        $userData=[];
        $x=0;
        foreach ($users as $key=>$user){

            $userData[$x]=$user->toArray();
            $userData[$x]['is_own']=$user->id==$request->user()->id?true:false;
            $userData[$x]['is_star']=1;
            $userData[$x]['chat_id']=optional(Chat::where(['first_user_id'=>$request->user()->id,'second_user_id'=>$user->id,'delete_from_first_user'=>0])
                ->orWhere(function($query) use($request,$user) {
                    $query->where(['second_user_id' => $request->user()->id, 'first_user_id' => $user->id, 'delete_from_second_user' => 0]);
                })->first())->id;
            $x=$x+1;

        }

        $users= $users=Cache::remember('free_top','10*60*60',function ()use($users_ids,$x) {
            return User::where('type', 'user')->where('is_active',1)->whereNotIn('users.id', $users_ids)->limit(45 - $x)->inRandomOrder()->get();
        });

        foreach ($users as $key=>$user){

            $userData[$x]=$user->toArray();
            $userData[$x]['is_own']=$user->id==$request->user()->id?true:false;
            $userData[$x]['is_star']=0;

            $userData[$x]['chat_id']=optional(Chat::where(['first_user_id'=>$request->user()->id,'second_user_id'=>$user->id,'delete_from_first_user'=>0])
                ->orWhere(function($query) use($request,$user) {
                    $query->where(['second_user_id' => $request->user()->id, 'first_user_id' => $user->id, 'delete_from_second_user' => 0]);
                })->first())->id;
            $x=$x+1;
        }

        $diamonds=UsersDiamond::where('user_id',$request->user()->id)->where('type',1)->sum('diamonds');
        $used=UsersDiamond::where('user_id',$request->user()->id)->where('type',0)->sum('diamonds');
        return response()->json([
            'status' => true,
            'code' => 200,
            'data'=>['users'=>$userData,
                'stars'=>StarsPrice::get()->toArray(),
                'diamonds'=>$diamonds-$used,
            ]


        ]);
    }
    function remainingTimes(Request $request){
        $users_count=User::join('user_stars','user_stars.user_id','=','users.id')
            ->where('user_stars.expired_at','>',Carbon::now('Asia/Riyadh'))
            ->latest('user_stars.expired_at')
            ->where('users.is_active',1)
            ->select('users.*')
            ->count();
        if($users_count<50)
            $remainig=0;
        elseif($users_count==50)
            $remainig=Carbon::now('Asia/Riyadh')->diffInHours(UserStar::oldest('expire_at')->first()->expire_at);
        else{
            $remainig=Carbon::now('Asia/Riyadh')->diffInHours(WaitingUserStar::oldest('expire_at')->first()->expire_at);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'data'=>$remainig


        ]);
    }
}
