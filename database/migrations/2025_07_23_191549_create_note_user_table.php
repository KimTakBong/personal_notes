<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('note_user', function (Blueprint $table) {
            $table->uuid('note_id');
            $table->uuid('user_id');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_updated')->default(false);
            $table->timestamp('last_shared_at')->nullable();
            $table->primary(['note_id', 'user_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('note_user');
    }
};
