<?php

declare(strict_types=1);

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
        $listsTable = config('focal-core.tables.lists', 'focal_lists');
        $membershipsTable = config('focal-core.tables.list_memberships', 'focal_list_memberships');

        Schema::create($listsTable, function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('entity_type')->default('contact')->index();
            $table->string('type')->default('static')->index(); // static vs active
            $table->json('criteria')->nullable();
            $table->foreignIdFor(UserModel::className(), 'created_by_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create($membershipsTable, function (Blueprint $table) use ($listsTable): void {
            $table->id();
            $table->foreignId('list_id')->constrained($listsTable)->cascadeOnDelete();
            $table->string('member_type');
            $table->unsignedBigInteger('member_id');
            $table->timestamp('added_at')->useCurrent();

            $table->index(['member_type', 'member_id'], 'focal_list_member_idx');
            $table->unique(['list_id', 'member_type', 'member_id'], 'focal_list_member_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('focal-core.tables.list_memberships', 'focal_list_memberships'));
        Schema::dropIfExists(config('focal-core.tables.lists', 'focal_lists'));
    }
};
