<?php

declare(strict_types=1);

use Focal\Core\Enums\LifecycleStage;
use Focal\Core\Support\UserModel;
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
        $tableName = config('focal-core.tables.contacts', 'focal_contacts');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('lifecycle_stage')->default(LifecycleStage::Lead->value)->index();
            $table->json('properties')->nullable();
            $table->foreignIdFor(UserModel::className(), 'owner_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('focal-core.tables.contacts', 'focal_contacts');

        Schema::dropIfExists($tableName);
    }
};
