<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->string('organization_name')->nullable()->after('user_type');
            $table->string('gstin', 20)->nullable()->after('organization_name');
            $table->text('organization_address')->nullable()->after('gstin');
            $table->string('kyc_document_path')->nullable()->after('organization_address');
            $table->string('kyc_status')->default('NotSubmitted')->after('kyc_document_path');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_status');
            $table->unsignedBigInteger('kyc_reviewed_by')->nullable()->after('kyc_rejection_reason');
            $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_reviewed_by');
            $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_reviewed_at');
        });
    }

    public function down()
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->dropColumn([
                'organization_name',
                'gstin',
                'organization_address',
                'kyc_document_path',
                'kyc_status',
                'kyc_rejection_reason',
                'kyc_reviewed_by',
                'kyc_reviewed_at',
                'kyc_submitted_at',
            ]);
        });
    }
};
