<?php

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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->enum('code', ['callback', 'calc', 'question'])->unique();
            $table->string('title');
            $table->boolean('is_active')->default(true);
            $table->json('notification_email');
            $table->json('notification_telegram');
            $table->enum('captcha_mode', ['none', 'recaptcha', 'hcaptcha'])->nullable();
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->enum('type', ['text', 'textarea', 'phone', 'email', 'select', 'checkbox', 'tel', 'radio', 'date', 'file'])->nullable();
            $table->string('mask')->nullable();
            $table->boolean('is_required');
            $table->integer('sort');
            $table->json('options')->nullable();
            $table->string('validation_rules')->nullable();
        });

        Schema::create('form_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->enum('placement', ['inline', 'modal', 'cta_block']);
            $table->boolean('is_enabled');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->enum('form_code', ['callback', 'calc', 'question']);
            $table->enum('status', ['new', 'in_progress', 'closed'])->default('new');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('payload');
            $table->text('source_url')->nullable();
            $table->string('page_title')->nullable();
            $table->json('utm')->nullable();
            $table->boolean('consent_given')->nullable();
            $table->string('consent_doc_url')->nullable();
            $table->dateTime('consent_at')->nullable();
            $table->text('manager_comment')->nullable();
            $table->timestamps();

            $table->index('form_code');
            $table->index('status');
            $table->index('phone');
            $table->index('email');
        });

        Schema::create('lead_dedup_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('contact_key');
            $table->date('created_date');

            $table->unique('lead_id');
            $table->index('contact_key');
            $table->index('created_date');
        });

        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', ['form_submit', 'conversion', 'click', 'page_view', 'form_interaction', 'engagement'])->nullable();
            $table->string('event_name')->nullable();
            $table->json('data')->nullable();
            $table->text('source_url')->nullable();
            $table->json('utm')->nullable();
            $table->string('client_id')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->text('page_url')->nullable();
            $table->text('referer')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_type');
            $table->index('event_name');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('lead_dedup_index');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('form_placements');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
