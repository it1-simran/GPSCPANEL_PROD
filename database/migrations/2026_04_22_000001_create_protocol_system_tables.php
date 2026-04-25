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
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('packet_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->string('name');
            $table->string('header_identifier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('packet_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packet_type_id')->constrained('packet_types')->onDelete('cascade');
            $table->string('name');
            $table->integer('sequence')->default(0);
            $table->string('data_type')->default('ASCII'); // ASCII, HEX, Numeric
            $table->string('length_type')->default('Fixed'); // Fixed, Variable
            $table->integer('length')->nullable();
            $table->string('format_rule')->nullable(); // regex or specific format name
            $table->string('fixed_value')->nullable();
            $table->string('validation_type')->default('none'); // none, checksum, imei, firmware
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('validated_packets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imei_log_id')->constrained('imei_logs')->onDelete('cascade');
            $table->foreignId('packet_type_id')->constrained('packet_types')->onDelete('cascade');
            $table->json('data');
            $table->boolean('is_valid')->default(true);
            $table->json('errors')->nullable();
            $table->timestamps();
        });

        Schema::table('imei_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('imei_devices', 'protocol_id')) {
                $table->foreignId('protocol_id')->nullable()->after('id')->constrained('protocols')->onDelete('set null');
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
        Schema::table('imei_devices', function (Blueprint $table) {
            $table->dropForeign(['protocol_id']);
            $table->dropColumn('protocol_id');
        });

        Schema::dropIfExists('validated_packets');
        Schema::dropIfExists('packet_fields');
        Schema::dropIfExists('packet_types');
        Schema::dropIfExists('protocols');
    }
};
