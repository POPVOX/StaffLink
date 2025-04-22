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
        Schema::create('faq_cluster_message', function (Blueprint $table) {
            // Cluster ↔ Message pivot
            $table->foreignId('cluster_id')
                ->constrained('faq_clusters')
                ->onDelete('cascade');
            $table->foreignId('message_id')
                ->constrained()
                ->onDelete('cascade');
            $table->primary(['cluster_id', 'message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_cluster_message');
    }
};
