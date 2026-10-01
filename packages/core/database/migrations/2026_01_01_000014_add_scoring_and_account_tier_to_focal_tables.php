<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead scoring and account tiering are used by core actions (merging, timeline
 * summaries, customer health), so core owns these columns. Earlier versions of
 * focalcrm/marketing created them, hence the guards for existing databases.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $contactsTable = config('focal-core.tables.contacts', 'focal_contacts');
        $companiesTable = config('focal-core.tables.companies', 'focal_companies');

        if (! Schema::hasColumn($contactsTable, 'lead_score')) {
            Schema::table($contactsTable, function (Blueprint $table): void {
                $table->integer('lead_score')->default(0)->after('lifecycle_stage');
                $table->timestamp('lead_score_updated_at')->nullable()->after('lead_score');
            });
        }

        if (! Schema::hasColumn($companiesTable, 'account_tier')) {
            Schema::table($companiesTable, function (Blueprint $table): void {
                $table->string('account_tier', 20)->nullable()->after('industry'); // tier_1, tier_2, tier_3
                $table->unsignedInteger('intent_score')->default(0)->after('account_tier');
                $table->boolean('intent_surge')->default(false)->after('intent_score');
                $table->unsignedInteger('buying_committee_size')->default(0)->after('intent_surge');
                $table->timestamp('last_intent_activity_at')->nullable()->after('buying_committee_size');

                $table->index(['account_tier', 'intent_score']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $contactsTable = config('focal-core.tables.contacts', 'focal_contacts');
        $companiesTable = config('focal-core.tables.companies', 'focal_companies');

        Schema::table($companiesTable, function (Blueprint $table): void {
            $table->dropIndex(['account_tier', 'intent_score']);
            $table->dropColumn(['account_tier', 'intent_score', 'intent_surge', 'buying_committee_size', 'last_intent_activity_at']);
        });

        Schema::table($contactsTable, function (Blueprint $table): void {
            $table->dropColumn(['lead_score', 'lead_score_updated_at']);
        });
    }
};
