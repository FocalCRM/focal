<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables for the Deal and Ticket fixtures (see CrossHubRelations). Created with the rest of the
 * schema rather than inside a test: MySQL commits implicitly on CREATE TABLE, which would end
 * RefreshDatabase's per-test transaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixture_deals', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('fixture_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('subject');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->boolean('is_sla_response_breached')->default(false);
            $table->boolean('is_sla_resolution_breached')->default(false);
            $table->unsignedTinyInteger('csat_rating')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixture_tickets');
        Schema::dropIfExists('fixture_deals');
    }
};
