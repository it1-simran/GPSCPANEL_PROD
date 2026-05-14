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
        Schema::table('test_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('protocol_id')->nullable()->after('description');
            
            // If you want a foreign key constraint:
            // $table->foreign('protocol_id')->references('id')->on('protocols')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_plans', function (Blueprint $table) {
            $table->dropColumn('protocol_id');
        });
    }
};
