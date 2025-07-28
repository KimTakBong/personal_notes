<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('note_user', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
            $table->boolean('is_updated')->default(false);
            $table->timestamp('last_shared_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('note_user', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'is_updated', 'last_shared_at']);
        });
    }
};
