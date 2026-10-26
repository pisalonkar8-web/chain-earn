<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_locks', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
        });
        DB::table('sim_locks')->insert(['id' => 1]);
        Schema::create('sim_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('role', 20);
        });
        Schema::create('sim_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('purchased');
            $table->timestamps();
        });
        Schema::create('sim_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->uuid('request_key')->unique();
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });
        Schema::create('sim_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('sim_purchases')->restrictOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained('sim_withdrawals')->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('type', 30);
            $table->integer('amount');
            $table->unsignedTinyInteger('level')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'id']);
        });
        Schema::create('sim_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 60);
            $table->string('subject', 100);
            $table->string('reason', 500);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['sim_audit', 'sim_ledger', 'sim_withdrawals', 'sim_purchases', 'sim_access', 'sim_locks'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
