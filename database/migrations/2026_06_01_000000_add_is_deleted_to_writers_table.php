<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('writers', function (Blueprint $table) {
            if (!Schema::hasColumn('writers', 'is_deleted')) {
                $table->boolean('is_deleted')->default(0)->after('Active_Status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('writers', function (Blueprint $table) {
            if (Schema::hasColumn('writers', 'is_deleted')) {
                $table->dropColumn('is_deleted');
            }
        });
    }
};
