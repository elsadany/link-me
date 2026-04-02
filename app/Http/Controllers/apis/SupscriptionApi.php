<?php

namespace App\Http\Controllers\apis;

use App\Models\Chat;
use App\Models\Product;
use App\Models\StarsPrice;
use App\Models\SupscriptionPlan;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UsersDiamond;
use App\Models\UsersParchase;
use App\Models\UserStar;
use App\Models\WaitingUserStar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class SupscriptionApi extends Controller
{
    private const CATALOG_TTL = 3600;

    private function catalogPlans(): array
    {
        return Cache::remember('catalog:supscription_plans_v1', self::CATALOG_TTL, function () {
            return SupscriptionPlan::query()->get()->toArray();
        });
    }

    private function catalogProducts(): array
    {
        return Cache::remember('catalog:products_v1', self::CATALOG_TTL, function () {
            return Product::query()->get()->toArray();
        });
    }

    private function catalogStars(): array
    {
        return Cache::remember('catalog:stars_prices_v1', self::CATALOG_TTL, function () {
            return StarsPrice::query()->get()->toArray();
        });
    }

    /**
     * @return array<int, int> other_user_id => chat_id
     */
    private function chatIdsForUserPairs(int $authId, array $otherUserIds): array
    {
        $otherUserIds = array_values(array_unique(array_map('intval', $otherUserIds)));
        if ($otherUserIds === []) {
            return [];
        }

        $chats = Chat::query()
            ->where(function ($q) use ($authId, $otherUserIds) {
                $q->where('first_user_id', $authId)
                    ->whereIn('second_user_id', $otherUserIds)
                    ->where('delete_from_first_user', 0);
            })
            ->orWhere(function ($q) use ($authId, $otherUserIds) {
                $q->where('second_user_id', $authId)
                    ->whereIn('first_user_id', $otherUserIds)
                    ->where('delete_from_second_user', 0);
            })
            ->get(['id', 'first_user_id', 'second_user_id']);

        $map = [];
        foreach ($chats as $chat) {
            $other = (int) ($chat->first_user_id == $authId ? $chat->second_user_id : $chat->first_user_id);
            $map[$other] = $chat->id;
        }

        return $map;
    }

    function plans(Request $request)
    {
        $uid = $request->user()->id;
        $active = UsersParchase::activeSubscriptionForUser($uid);

        $last_supscription_data = ['is_subscribed' => 0, 'is_finished' => 0, 'finish_at' => null];
        if ($active) {
            $last_supscription_data = ['is_subscribed' => 1, 'is_finished' => 0, 'finish_at' => $active->finish_at];
        } elseif (UsersParchase::query()->where('user_id', $uid)->exists()) {
            $last_supscription_data = ['is_subscribed' => 1, 'is_finished' => 1, 'finish_at' => null];
        }

        $supscription = $active ? $active->toArray() : null;

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'plans' => $this->catalogPlans(),
                'products' => $this->catalogProducts(),
                'stars' => $this->catalogStars(),
                'diamonds' => UsersDiamond::netBalanceForUser($uid),
                'last_supscription_data' => $last_supscription_data,
                'last_supscription' => $supscription,
            ],
        ]);
    }

    function products(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $this->catalogProducts(),
        ]);
    }

    function stars(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $this->catalogStars(),
        ]);
    }

    function getLastSupscription(Request $request)
    {
        $last_supscription = UsersParchase::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        $supscription = [];
        if ($last_supscription) {
            $plan = SupscriptionPlan::query()->find($last_supscription->subscription_plan_id);
            $supscription = $plan ? $plan->toArray() : [];
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $supscription,
        ]);
    }

    function getSupscriptionStatus(Request $request)
    {
        $active = UsersParchase::activeSubscriptionForUser($request->user()->id);

        if ($active) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'subscribed',
                'data' => ['is_subscribed' => 1, 'is_finished' => 0, 'finish_at' => $active->finish_at],
            ]);
        }

        if (UsersParchase::query()->where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'finished',
                'data' => ['is_subscribed' => 1, 'is_finished' => 1, 'finish_at' => null],
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'finished',
            'data' => ['is_subscribed' => 0, 'is_finished' => 0, 'finish_at' => null],
        ]);
    }

    function buySubscription(Request $request)
    {
        $request->validate([
            'supscription_plan_id' => 'required|exists:supscription_plans,id',
            'transction_id' => 'required',
            'paid_by' => 'required|in:android,ios',
        ]);

        $supscription_plan = SupscriptionPlan::find($request->supscription_plan_id);
        $last_supscription = UsersParchase::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('finish_at', '>=', Carbon::now('Asia/Riyadh'))
            ->first();

        $expire = Carbon::now('Asia/Riyadh')->addDays($supscription_plan->days);
        if ($last_supscription) {
            $expire = Carbon::parse($last_supscription->finish_at)->addDays($supscription_plan->days);
        }

        UsersParchase::create([
            'paid_at' => Carbon::now('Asia/Riyadh'),
            'finish_at' => $expire,
            'subscription_plan_id' => $supscription_plan->id,
            'paid' => $supscription_plan->price,
            'user_id' => $request->user()->id,
            'transaction_id' => $request->input('transaction_id', $request->input('transction_id')),
            'paid_by' => $request->paid_by,
        ]);

        UsersDiamond::create([
            'user_id' => $request->user()->id,
            'diamonds' => $supscription_plan->diamonds,
            'type' => 1,
            'paid_at' => null,
            'product_id' => null,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.subscribed'),
        ]);
    }

    function getDiamonds(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => UsersDiamond::netBalanceForUser($request->user()->id),
        ]);
    }

    function buyDiamonds(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_id' => 'required',
            'paid_by' => 'required|in:android,ios',
        ]);

        $product = Product::find($request->product_id);
        UsersDiamond::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'paid_by' => $request->paid_by,
            'paid_at' => Carbon::now('Asia/Riyadh'),
            'type' => 1,
            'diamonds' => $product->number,
            'amount' => $product->price,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => UsersDiamond::netBalanceForUser($request->user()->id),
        ]);
    }

    function buyStar(Request $request)
    {
        $request->validate([
            'star_price_id' => 'required|exists:stars_prices,id',
        ]);

        $starPrice = StarsPrice::find($request->star_price_id);
        $userDiamonds = UsersDiamond::netBalanceForUser($request->user()->id);

        if ($userDiamonds < $starPrice->diamonds) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'you don`t have enough diamonds',
                'errors' => new \stdClass(),
            ]);
        }

        $users_count = User::query()
            ->join('user_stars', 'user_stars.user_id', '=', 'users.id')
            ->where('user_stars.expired_at', '>', Carbon::now('Asia/Riyadh'))
            ->count('users.id');

        if ($users_count <= 50) {
            UsersDiamond::create([
                'diamonds' => $starPrice->diamonds,
                'user_id' => $request->user()->id,
                'type' => 0,
            ]);
            UserStar::create([
                'user_id' => $request->user()->id,
                'expired_at' => Carbon::now('Asia/Riyadh')->addHours($starPrice->hours),
                'star_price_id' => $request->star_price_id,
            ]);
        } else {
            $last = UserStar::oldest('expired_at')->first();
            if (! $last) {
                return response()->json([
                    'status' => false,
                    'code' => 500,
                    'message' => 'queue unavailable',
                ]);
            }

            $begin = Carbon::parse($last->expired_at);
            $nextExpire = (clone $begin)->addHours($starPrice->hours);

            UsersDiamond::create([
                'diamonds' => $starPrice->diamonds,
                'user_id' => $request->user()->id,
                'type' => 0,
            ]);

            WaitingUserStar::create([
                'user_id' => $request->user()->id,
                'expired_at' => $nextExpire,
                'begin_at' => $begin,
                'star_price_id' => $request->star_price_id,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('home.subscribed'),
        ]);
    }

    function topUsers(Request $request)
    {
        $authId = $request->user()->id;

        $blockRows = UserBlock::query()
            ->where('user_id', $authId)
            ->orWhere('friend_id', $authId)
            ->get(['user_id', 'friend_id']);
        $users_ids = $blockRows
            ->map(fn ($b) => (int) ($b->user_id == $authId ? $b->friend_id : $b->user_id))
            ->unique()
            ->values()
            ->all();

        $ttl = 600;

        $paidUsers = Cache::remember('top_users_paid_'.$authId, $ttl, function () use ($users_ids) {
            return User::query()
                ->where('is_active', 1)
                ->join('user_stars', 'user_stars.user_id', '=', 'users.id')
                ->where('user_stars.expired_at', '>=', Carbon::now('Asia/Riyadh'))
                ->latest('user_stars.expired_at')
                ->whereNotIn('users.id', $users_ids)
                ->select('users.*')
                ->get();
        });

        $userData = [];
        $x = 0;

        $paidOtherIds = $paidUsers->pluck('id')->all();
        $chatMap = $this->chatIdsForUserPairs($authId, $paidOtherIds);

        foreach ($paidUsers as $user) {
            $userData[$x] = $user->toArray();
            $userData[$x]['is_own'] = $user->id == $authId;
            $userData[$x]['is_star'] = 1;
            $userData[$x]['chat_id'] = $chatMap[$user->id] ?? null;
            $x++;
        }

        $freeUsers = Cache::remember('top_users_free_'.$authId.'_'.$x, $ttl, function () use ($users_ids, $x) {
            return User::query()
                ->where('type', 'user')
                ->where('is_active', 1)
                ->whereNotIn('users.id', $users_ids)
                ->limit(max(0, 45 - $x))
                ->inRandomOrder()
                ->get();
        });

        $freeOtherIds = $freeUsers->pluck('id')->all();
        $chatMapFree = $this->chatIdsForUserPairs($authId, $freeOtherIds);

        foreach ($freeUsers as $user) {
            $userData[$x] = $user->toArray();
            $userData[$x]['is_own'] = $user->id == $authId;
            $userData[$x]['is_star'] = 0;
            $userData[$x]['chat_id'] = $chatMapFree[$user->id] ?? null;
            $x++;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => [
                'users' => $userData,
                'stars' => $this->catalogStars(),
                'diamonds' => UsersDiamond::netBalanceForUser($authId),
            ],
        ]);
    }

    function remainingTimes(Request $request)
    {
        $users_count = User::query()
            ->join('user_stars', 'user_stars.user_id', '=', 'users.id')
            ->where('user_stars.expired_at', '>', Carbon::now('Asia/Riyadh'))
            ->where('users.is_active', 1)
            ->count('users.id');

        $now = Carbon::now('Asia/Riyadh');

        if ($users_count < 50) {
            $remainig = 0;
        } elseif ($users_count == 50) {
            $oldest = UserStar::oldest('expired_at')->first();
            $remainig = $oldest ? $now->diffInHours($oldest->expired_at) : 0;
        } else {
            $waiting = WaitingUserStar::oldest('expired_at')->first();
            $remainig = $waiting ? $now->diffInHours($waiting->expired_at) : 0;
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $remainig,
        ]);
    }
}
