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
        $tableName = config('focal-core.tables.companies', 'focal_companies');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->string('domain')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('industry')->nullable()->index();
            $table->json('properties')->nullable();
            $table->foreignIdFor(UserModel::className(), 'owner_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('focal-core.tables.companies', 'focal_companies');

        Schema::dropIfExists($tableName);
    }
};
