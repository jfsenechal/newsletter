<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('address_book_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission')->default('read');
            $table->timestamps();

            $table->unique(['address_book_id', 'user_id']);
        });
    }
};
