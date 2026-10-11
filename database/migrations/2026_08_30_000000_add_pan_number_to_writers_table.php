<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->string('pan_number', 10)->nullable()->after('gstin');
        });
    }

    public function down()
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->dropColumn('pan_number');
        });
    }
};
