<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_parchases', function (Blueprint $table) {
            $table->id();
            $table->dateTime('finish_at');
            $table->bigInteger('student_id');
            $table->double('paid');
            $table->string('purchase_type');
            $table->dateTime('paid_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_parchases');
    }
};
