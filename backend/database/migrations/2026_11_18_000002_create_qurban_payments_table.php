<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qurban_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurban_registration_id')
                ->constrained('qurban_registrations')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('payment_number');
            $table->unsignedBigInteger('amount');
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['qurban_registration_id', 'payment_number']);
        });

        DB::table('qurban_registrations')
            ->where('status', 'lunas')
            ->orderBy('id')
            ->each(function ($registration) {
                DB::table('qurban_payments')->insert([
                    'qurban_registration_id' => $registration->id,
                    'payment_number' => 1,
                    'amount' => $registration->jumlah,
                    'paid_at' => $registration->created_at ?? now(),
                    'note' => 'Migrasi pembayaran lunas terdahulu',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('qurban_payments');
    }
};
