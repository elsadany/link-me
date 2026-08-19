<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users_diamonds')) {
            Schema::table('users_diamonds', function (Blueprint $table) {
                if (! Schema::hasColumn('users_diamonds', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('users_diamonds', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('users_diamonds', 'amount')) {
                    $table->double('amount')->nullable();
                }
                if (! Schema::hasColumn('users_diamonds', 'paid_by')) {
                    $table->string('paid_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('users_parchases')) {
            Schema::table('users_parchases', function (Blueprint $table) {
                if (! Schema::hasColumn('users_parchases', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('users_parchases', 'subscription_plan_id')) {
                    $table->unsignedBigInteger('subscription_plan_id')->nullable();
                }
                if (! Schema::hasColumn('users_parchases', 'transaction_id')) {
                    $table->string('transaction_id')->nullable();
                }
                if (! Schema::hasColumn('users_parchases', 'paid_by')) {
                    $table->string('paid_by')->nullable();
                }
            });

            if (Schema::hasColumn('users_parchases', 'student_id') && Schema::hasColumn('users_parchases', 'user_id')) {
                DB::table('users_parchases')
                    ->whereNull('user_id')
                    ->update(['user_id' => DB::raw('student_id')]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users_diamonds')) {
            Schema::table('users_diamonds', function (Blueprint $table) {
                foreach (['paid_by', 'amount', 'product_id', 'user_id'] as $column) {
                    if (Schema::hasColumn('users_diamonds', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users_parchases')) {
            Schema::table('users_parchases', function (Blueprint $table) {
                foreach (['paid_by', 'transaction_id', 'subscription_plan_id', 'user_id'] as $column) {
                    if (Schema::hasColumn('users_parchases', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
