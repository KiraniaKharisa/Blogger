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
        Schema::table('artikels', function (Blueprint $table) {
            $table->foreign('kategori_id')
            ->references('id')
            ->on('kategoris')
            ->onDelete('restrict') 
            ->onUpdate('restrict');
            
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('restrict') 
            ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            //
        });
    }
};
