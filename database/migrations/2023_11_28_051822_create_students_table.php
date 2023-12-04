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
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->bigInteger('class_id')->unsigned();
            $table->string('student_name');
            $table->string('birth_day');
            $table->string('gender');
            $table->longText('address');
            $table->string('image')->nullable(); // Make the image column nullable
            $table->timestamps();

            $table->foreign('class_id')->references('class_id')->on('classrooms')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
