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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            // user_id FK to users.id (default)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->enum('intity', ['الكهرباء', 'المياه', 'البلدية', 'المالية', 'العقارية']);
            $table->string('city');
            $table->string('attachments')->nullable();
            $table->text('description');
            $table->boolean('is_emergency')->default(false);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('map_iframe')->nullable();

            // category_id FK to categories.category_id (non-standard PK)
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
