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
        $tableName = config('odden-core.tables.property_history', 'odden_property_history');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('property_name')->index();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignIdFor(UserModel::className(), 'user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('web')->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['auditable_type', 'auditable_id', 'created_at'], 'odden_audit_target_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('odden-core.tables.property_history', 'odden_property_history');

        Schema::dropIfExists($tableName);
    }
};
