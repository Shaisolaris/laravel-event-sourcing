<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('customer_id')->index();
            $table->string('status')->default('placed')->index();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->text('shipping_address');
            $table->text('billing_address');
            $table->string('coupon_code')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->string('estimated_delivery')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_id');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->string('author_id');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
        });

        Schema::create('daily_order_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('orders_placed')->default(0);
            $table->integer('orders_cancelled')->default(0);
            $table->integer('orders_delivered')->default(0);
            $table->bigInteger('revenue')->default(0);
            $table->bigInteger('refunds')->default(0);
        });
    }

    public function down(): void {
        Schema::dropIfExists('daily_order_stats');
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
