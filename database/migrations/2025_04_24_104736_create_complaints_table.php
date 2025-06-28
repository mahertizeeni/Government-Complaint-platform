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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('government_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('attachments')->nullable();
            $table->text('description');
            $table->enum('is_emergency', ['1', '2', '3'])->default('1');
            $table->boolean('anonymous')->default(false);
            $table->string('status')->default('pending');
            $table->text('map_iframe')->nullable();
            $table->timestamps();

        });
    }
public function down(): void
{Schema::table('complaints', function (Blueprint $table) {
    $table->dropForeign(['city_id']);
});
Schema::dropIfExists('complaints');


}
};
