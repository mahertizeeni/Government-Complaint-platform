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
        Schema::create('complaint_models', function (Blueprint $table) {
            $table->id(); // معرف الشكوى
            $table->string('title'); // عنوان الشكوى
            $table->text('description'); // وصف الشكوى
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // معرف المستخدم المرتبط
            $table->timestamps(); // تاريخ الإنشاء والتحديث
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_models');
    }
};
