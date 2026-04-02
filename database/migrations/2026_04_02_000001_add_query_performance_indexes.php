<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users_diamonds', function (Blueprint $table) {
            $table->index(['user_id', 'type'], 'users_diamonds_user_id_type_index');
        });

        Schema::table('users_parchases', function (Blueprint $table) {
            $table->index(['user_id', 'finish_at'], 'users_parchases_user_id_finish_at_index');
        });

        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->index(['chat_id', 'read', 'sender_id'], 'chat_messages_chat_read_sender_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users_diamonds', function (Blueprint $table) {
            $table->dropIndex('users_diamonds_user_id_type_index');
        });

        Schema::table('users_parchases', function (Blueprint $table) {
            $table->dropIndex('users_parchases_user_id_finish_at_index');
        });

        if (Schema::hasTable('chat_messages')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropIndex('chat_messages_chat_read_sender_index');
            });
        }
    }
};
