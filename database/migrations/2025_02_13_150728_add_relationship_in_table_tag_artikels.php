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
        Schema::table('tag_artikels', function (Blueprint $table) {
            $table->foreign('artikel_id')
            ->references('id')
            ->on('artikels')
            ->onDelete('restrict') 
            ->onUpdate('restrict');

            $table->foreign('tag_id')
            ->references('id')
            ->on('tags')
            ->onDelete('restrict') 
            ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tag_artikels', function (Blueprint $table) {
            //
        });
    }
};
