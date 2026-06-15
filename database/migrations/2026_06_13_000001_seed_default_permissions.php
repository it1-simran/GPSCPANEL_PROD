<?php

use Database\Seeders\Support\DefaultPermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DefaultPermissions::seed();
        DefaultPermissions::seedDependencies();
    }

    public function down(): void
    {
        // Data seed migration — do not delete production permission rows on rollback.
    }
};
