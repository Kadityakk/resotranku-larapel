<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest customers order by scanning a table QR code, so orders are no longer
     * tied to a user account. Status columns move from enum to string so the
     * order lifecycle can grow without an ALTER on every new state.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('customer_name')->after('user_id');
            $table->string('customer_phone')->after('customer_name');
            $table->string('status')->default('pending')->change();
            $table->string('payment_method')->default('cash')->change();
            $table->string('payment_status')->default('unpaid')->after('payment_method');
            $table->string('snap_token', 512)->nullable()->after('payment_status');
            $table->string('midtrans_transaction_id')->nullable()->after('snap_token');
            $table->timestamp('paid_at')->nullable()->after('midtrans_transaction_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('payment_status');
            $table->index('table_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['table_number']);
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'payment_status',
                'snap_token',
                'midtrans_transaction_id',
                'paid_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->enum('status', ['pending', 'settlement', 'cooked'])->change();
            $table->enum('payment_method', ['cash', 'qris'])->change();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
