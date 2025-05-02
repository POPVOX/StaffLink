<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->string('question_pattern')->unique()
                ->comment('A short “example” phrase to match against incoming queries');
            $table->text('answer_text')
                ->comment('The canonical override answer to use when matched');
            $table->integer('priority')->default(0)
                ->comment('Higher numbers override lower when multiple match');
            $table->boolean('active')->default(true)
                ->comment('Toggle this correction on/off');
            $table->json('example_embedding')->nullable()
                ->comment('Raw embedding for the question_pattern');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrections');
    }
};
