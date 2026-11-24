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
        // 1. SUPPLIERS
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. CUSTOMERS
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 3. PRODUCTS (Máy tính và linh kiện máy tính)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('cpu')->nullable();
            $table->string('ram')->nullable();
            $table->string('storage')->nullable();
            $table->string('gpu')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0);      // Giá vốn bình quân
            $table->decimal('selling_price', 15, 2)->default(0);   // Giá bán niêm yết
            $table->integer('quantity')->default(0);               // Số lượng tồn hiện tại
            $table->integer('min_quantity')->default(5);           // Ngưỡng cảnh báo tồn thấp
            $table->boolean('status')->default(true);              // Hoạt động / Ngừng KD
            $table->timestamps();
        });

        // 4. INBOUND_NOTES (Phiếu nhập kho)
        Schema::create('inbound_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                      // PNxxxx
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('created_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('status')->default('completed');        // completed, cancelled
            $table->timestamps();
        });

        // 5. INBOUND_DETAILS (Chi tiết phiếu nhập)
        Schema::create('inbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_note_id')->constrained('inbound_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('import_price', 15, 2);
            $table->timestamps();
        });

        // 6. OUTBOUND_NOTES (Phiếu xuất kho)
        Schema::create('outbound_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                      // PXxxxx
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('created_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('status')->default('completed');        // completed, cancelled
            $table->timestamps();
        });

        // 7. OUTBOUND_DETAILS (Chi tiết phiếu xuất)
        Schema::create('outbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_note_id')->constrained('outbound_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('export_price', 15, 2);
            $table->timestamps();
        });

        // 8. STOCK_CARDS (Thẻ kho - Lịch sử biến động tồn kho)
        Schema::create('stock_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('transaction_type');                    // inbound, outbound, adjust, cancel_inbound, cancel_outbound
            $table->string('ref_code');                            // PN001, PX001, DC001...
            $table->integer('change_quantity');                    // +10, -5, +2, -1
            $table->integer('balance_quantity');                   // Số dư tồn sau giao dịch
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_cards');
        Schema::dropIfExists('outbound_details');
        Schema::dropIfExists('outbound_notes');
        Schema::dropIfExists('inbound_details');
        Schema::dropIfExists('inbound_notes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('suppliers');
    }
};
