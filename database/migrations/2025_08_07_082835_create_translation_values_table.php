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
        Schema::create('translation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained()->onDelete('cascade');
            $table->foreignId('locale_id')->constrained()->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_values');
    }
};
