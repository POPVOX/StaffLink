<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_keyword', function (Blueprint $table) {
            $table->foreignId('correction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('keyword_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->primary(['correction_id', 'keyword_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_keyword');
    }
};
