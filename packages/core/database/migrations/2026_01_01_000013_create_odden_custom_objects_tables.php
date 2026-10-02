<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Odden\Core\Support\UserModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $definitionsTable = config('odden-core.tables.custom_object_definitions', 'odden_custom_object_definitions');
        $recordsTable = config('odden-core.tables.custom_object_records', 'odden_custom_object_records');

        Schema::create($definitionsTable, function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('singular_label');
            $table->string('plural_label');
            $table->text('description')->nullable();
            $table->string('primary_display_property')->default('name');
            $table->json('secondary_display_properties')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create($recordsTable, function (Blueprint $table) use ($definitionsTable): void {
            $table->id();
            $table->foreignId('definition_id')->constrained($definitionsTable)->cascadeOnDelete();
            $table->string('name')->index();
            $table->json('properties')->nullable();
            $table->foreignIdFor(UserModel::className(), 'owner_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $definitionsTable = config('odden-core.tables.custom_object_definitions', 'odden_custom_object_definitions');
        $recordsTable = config('odden-core.tables.custom_object_records', 'odden_custom_object_records');

        Schema::dropIfExists($recordsTable);
        Schema::dropIfExists($definitionsTable);
    }
};
