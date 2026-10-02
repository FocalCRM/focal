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
        $tableName = config('odden-core.tables.associations', 'odden_associations');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            // Lengths keep the unique index under MySQL's 3072-byte key limit with utf8mb4.
            $table->string('parent_type', 191);
            $table->unsignedBigInteger('parent_id');
            $table->string('child_type', 191);
            $table->unsignedBigInteger('child_id');
            $table->string('type', 100)->default('default');
            $table->timestamps();

            $table->index(['parent_type', 'parent_id'], 'odden_assoc_parent_idx');
            $table->index(['child_type', 'child_id'], 'odden_assoc_child_idx');
            $table->unique(
                ['parent_type', 'parent_id', 'child_type', 'child_id', 'type'],
                'odden_assoc_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('odden-core.tables.associations', 'odden_associations');

        Schema::dropIfExists($tableName);
    }
};
