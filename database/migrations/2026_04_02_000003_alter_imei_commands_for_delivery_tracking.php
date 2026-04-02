<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            if (!Schema::hasColumn('imei_commands', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
            $table->index(['imei_id', 'status']);
        });
    }

    public function down()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            $table->dropIndex(['imei_id', 'status']);
            if (Schema::hasColumn('imei_commands', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
        });
    }
};
