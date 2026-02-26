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
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();

            $table->enum('source', ['website','email','telefoon','whatsapp','showroom','overig']);
            $table->enum('status', ['nieuw','opgepakt','proefrit','offerte','verkocht','afgevallen']);
            $table->timestamps();

            $table->index('status');
            $table->index(['email','status']);
        });

        DB::statement("ALTER TABLE leads ADD CONSTRAINT chk_name_length CHECK (CHAR_LENGTH(name) >= 2)");
        DB::statement("ALTER TABLE leads ADD CONSTRAINT chk_email_format CHECK (email LIKE '%_@_%._%')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
