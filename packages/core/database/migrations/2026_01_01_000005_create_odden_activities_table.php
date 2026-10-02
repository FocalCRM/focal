<?php

declare(strict_types=1);

use Odden\Core\Enums\ActivityStatus;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Support\UserModel;
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
        $tableName = config('odden-core.tables.activities', 'odden_activities');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('type')->default(ActivityType::Note->value);
            $table->string('status')->default(ActivityStatus::Completed->value);
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignIdFor(UserModel::className(), 'creator_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'created_at'], 'odden_activity_subject_idx');
            $table->index(['type', 'status'], 'odden_activity_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('odden-core.tables.activities', 'odden_activities');

        Schema::dropIfExists($tableName);
    }
};
