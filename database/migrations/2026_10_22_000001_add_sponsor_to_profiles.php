<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Preserve attribution: sponsors with referred members cannot be deleted.
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', fn (Blueprint $table) => $table->dropConstrainedForeignId('sponsor_id'));
    }
};
