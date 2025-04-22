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
        Schema::create('faq_clusters', function (Blueprint $table) {
            $table->id();
            // A human‐readable “best” question from this cluster
            $table->text('representative_text');
            // Total number of messages in this cluster
            $table->unsignedInteger('frequency')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_clusters');
    }
};
