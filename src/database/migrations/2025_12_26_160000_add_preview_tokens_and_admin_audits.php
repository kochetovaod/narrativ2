<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'pages',
            'product_categories',
            'products',
            'services',
            'portfolio_cases',
            'news_posts',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->uuid('preview_token')->nullable()->unique()->after('slug');
            });
        }

        foreach ($tables as $table) {
            DB::table($table)
                ->whereNull('preview_token')
                ->orderBy('id')
                ->chunkById(100, function ($items) use ($table): void {
                    foreach ($items as $item) {
                        DB::table($table)
                            ->where('id', $item->id)
                            ->update(['preview_token' => Str::uuid()->toString()]);
                    }
                });
        }

        Schema::create('admin_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('changes')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audits');

        $tables = [
            'pages',
            'product_categories',
            'products',
            'services',
            'portfolio_cases',
            'news_posts',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropUnique($tableName.'_preview_token_unique');
                $table->dropColumn('preview_token');
            });
        }
    }
};
