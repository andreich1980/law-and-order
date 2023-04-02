<?php

use App\Models\User;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TelegraphChat::class);
            $table->string('last_name', 20)->nullable();
            $table->string('first_name', 20)->nullable();
            $table->string('middle_name', 20)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};
