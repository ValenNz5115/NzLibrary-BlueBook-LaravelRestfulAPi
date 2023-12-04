<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('loans', function (Blueprint $table) {
        $table->id('loan_id');
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('book_id');
        $table->date('loan_date');
        $table->date('return_date')->nullable(); // Allow NULL for return_date
        $table->enum('status', ['not_returned', 'returned'])->default('not_returned');
        $table->enum('status_payment', ['not_fined', 'penalty'])->default('not_fined');
        $table->integer('penalty')->default(0);
        $table->timestamps();

        $table->foreign('student_id')->references('student_id')->on('students');
        $table->foreign('book_id')->references('book_id')->on('books');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
