<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('users_diamonds')
            && Schema::hasColumn('users_diamonds', 'user_id')
            && Schema::hasColumn('users_diamonds', 'type')
            && ! $this->indexExists('users_diamonds', 'users_diamonds_user_id_type_index')
        ) {
            Schema::table('users_diamonds', function (Blueprint $table) {
                $table->index(['user_id', 'type'], 'users_diamonds_user_id_type_index');
            });
        }

        if (
            Schema::hasTable('users_parchases')
            && Schema::hasColumn('users_parchases', 'user_id')
            && Schema::hasColumn('users_parchases', 'finish_at')
            && ! $this->indexExists('users_parchases', 'users_parchases_user_id_finish_at_index')
        ) {
            Schema::table('users_parchases', function (Blueprint $table) {
                $table->index(['user_id', 'finish_at'], 'users_parchases_user_id_finish_at_index');
            });
        }

        if (
            Schema::hasTable('chat_messages')
            && Schema::hasColumn('chat_messages', 'chat_id')
            && Schema::hasColumn('chat_messages', 'read')
            && Schema::hasColumn('chat_messages', 'sender_id')
            && ! $this->indexExists('chat_messages', 'chat_messages_chat_read_sender_index')
        ) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->index(['chat_id', 'read', 'sender_id'], 'chat_messages_chat_read_sender_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users_diamonds') && $this->indexExists('users_diamonds', 'users_diamonds_user_id_type_index')) {
            Schema::table('users_diamonds', function (Blueprint $table) {
                $table->dropIndex('users_diamonds_user_id_type_index');
            });
        }

        if (Schema::hasTable('users_parchases') && $this->indexExists('users_parchases', 'users_parchases_user_id_finish_at_index')) {
            Schema::table('users_parchases', function (Blueprint $table) {
                $table->dropIndex('users_parchases_user_id_finish_at_index');
            });
        }

        if (Schema::hasTable('chat_messages') && $this->indexExists('chat_messages', 'chat_messages_chat_read_sender_index')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropIndex('chat_messages_chat_read_sender_index');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$index]
        );

        return count($rows) > 0;
    }
};
