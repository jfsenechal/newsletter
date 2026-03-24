<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->string('status')->default('draft');
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->timestamps();
        });
    }
};
