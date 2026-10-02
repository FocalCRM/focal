<?php

declare(strict_types=1);

use Odden\Core\Enums\PropertyType;
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
        $tableName = config('odden-core.tables.properties', 'odden_properties');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type')->index();
            $table->string('name');
            $table->string('label');
            $table->string('type')->default(PropertyType::Text->value);
            $table->string('group_name')->default('general')->index();
            $table->json('options')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['entity_type', 'name'], 'odden_entity_prop_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('odden-core.tables.properties', 'odden_properties');

        Schema::dropIfExists($tableName);
    }
};
