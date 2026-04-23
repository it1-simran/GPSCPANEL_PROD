<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('validated_packets', function (Blueprint $table) {
            if (!Schema::hasColumn('validated_packets', 'imei_log_id')) {
                $table->unsignedBigInteger('imei_log_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('validated_packets', 'data')) {
                $table->json('data')->nullable()->after('packet_type_id');
            }
            if (!Schema::hasColumn('validated_packets', 'errors')) {
                $table->json('errors')->nullable()->after('is_valid');
            }
        });

        Schema::table('validated_packets', function (Blueprint $table) {
            if (Schema::hasColumn('validated_packets', 'raw_data')) {
                $table->dropColumn('raw_data');
            }
            if (Schema::hasColumn('validated_packets', 'parsed_data')) {
                $table->dropColumn('parsed_data');
            }
            if (Schema::hasColumn('validated_packets', 'imei')) {
                $table->dropColumn('imei');
            }
        });
    }

    public function down()
    {
        Schema::table('validated_packets', function (Blueprint $table) {
            if (!Schema::hasColumn('validated_packets', 'imei')) {
                $table->string('imei')->nullable();
            }
            if (!Schema::hasColumn('validated_packets', 'raw_data')) {
                $table->text('raw_data')->nullable();
            }
            if (!Schema::hasColumn('validated_packets', 'parsed_data')) {
                $table->json('parsed_data')->nullable();
            }
        });
    }
};
