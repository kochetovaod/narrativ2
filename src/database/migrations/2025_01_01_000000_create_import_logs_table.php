<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->comment('Тип сущности (products, services, etc.)');
            $table->string('operation_type')->comment('Тип операции (create, update, upsert, export)');
            $table->string('status')->default('pending')->comment('Статус (pending, processing, completed, failed, cancelled)');
            $table->integer('total_records')->default(0)->comment('Общее количество записей');
            $table->integer('processed_records')->default(0)->comment('Количество обработанных записей');
            $table->integer('error_records')->default(0)->comment('Количество записей с ошибками');
            $table->string('file_path')->nullable()->comment('Путь к файлу');
            $table->json('error_log')->nullable()->comment('Лог ошибок');
            $table->timestamp('started_at')->nullable()->comment('Время начала');
            $table->timestamp('finished_at')->nullable()->comment('Время завершения');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('ID пользователя');
            $table->timestamps();

            $table->index(['entity_type', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
