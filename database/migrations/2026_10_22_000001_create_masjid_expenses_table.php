<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_expenses', function (Blueprint $table) {
            $table->id();
            
            // Scope: Linked to Masjid or Madarsa
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');
            
            // Optional Campaign Association (null = General Masjid Operational Expense)
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->nullOnDelete();
            
            // Audit Trail: Who recorded this transaction
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Transaction Details
            $table->enum('entry_type', ['expense', 'income'])->default('expense');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('category')->default('other'); 
            // Examples: utilities, maintenance, salary, construction, supplies, events, general_donation, other
            
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque', 'other'])->default('cash');
            $table->date('transaction_date');
            
            $table->string('vendor_name')->nullable(); // Payee or Contributor
            $table->string('receipt_url')->nullable(); // Uploaded bill / voucher image path
            $table->text('notes')->nullable();
            
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('approved');
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast querying & aggregations
            $table->index(['masjid_id', 'entry_type', 'transaction_date']);
            $table->index(['masjid_id', 'campaign_id']);
            $table->index(['madarsa_id', 'entry_type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_expenses');
    }
};
