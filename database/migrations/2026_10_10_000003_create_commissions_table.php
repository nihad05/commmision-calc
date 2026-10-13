<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('fee', 16, 4);
            $table->char('currency', 3);
            $table->string('commission_type');
            $table->timestamp('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
