<?php

declare(strict_types=1);

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
        $tableName = config('focal-core.tables.companies', 'focal_companies');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->unsignedSmallInteger('health_score')->default(70)->after('intent_surge');
            $table->string('health_status', 20)->default('healthy')->after('health_score');
            $table->timestamp('last_health_calculated_at')->nullable()->after('health_status');

            $table->index(['health_status', 'health_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('focal-core.tables.companies', 'focal_companies');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropIndex(['health_status', 'health_score']);
            $table->dropColumn(['health_score', 'health_status', 'last_health_calculated_at']);
        });
    }
};
