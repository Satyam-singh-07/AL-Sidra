# Backend Implementation Plan: Masjid Expense & Financial Management

## 1. Executive Summary
This document outlines the architecture and step-by-step implementation plan for the **Expense & Financial Management** module in the Laravel backend (`AL-Sidra`). 

It provides full support for:
- Tracking operational money spent (outflows) and received (inflows) for the Masjid.
- Linking expenses directly to specific **Donation Campaigns** or categorizing them as **General Masjid Operations**.
- Comprehensive accounting aggregations: Total Collections, Total Expenses, Net Operating Balances, and Campaign-specific surplus/deficit.
- Receipt/voucher image uploads and audit logs.

---

## 2. Database Schema & Migration

### Migration File: `database/migrations/2026_10_22_000001_create_masjid_expenses_table.php`

```php
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
            
            $table->string('vendor_name')->nullable(); // Payee (e.g. Electric Board, Hardware Store) or Contributor
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
```

---

## 3. Eloquent Models & Relationships

### 3.1 New Model: `app/Models/MasjidExpense.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasjidExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'masjid_id',
        'madarsa_id',
        'campaign_id',
        'recorded_by_user_id',
        'entry_type',
        'title',
        'amount',
        'category',
        'payment_mode',
        'transaction_date',
        'vendor_name',
        'receipt_url',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // Relationships
    public function masjid()
    {
        return $this->belongsTo(Masjid::class);
    }

    public function madarsa()
    {
        return $this->belongsTo(Madarsa::class);
    }

    public function campaign()
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    // Scopes
    public function scopeExpenses($query)
    {
        return $query->where('entry_type', 'expense');
    }

    public function scopeIncomes($query)
    {
        return $query->where('entry_type', 'income');
    }

    public function scopeForPlace($query, $masjidId = null, $madarsaId = null)
    {
        return $query->when($masjidId, fn($q) => $q->where('masjid_id', $masjidId))
                     ->when($madarsaId, fn($q) => $q->where('madarsa_id', $madarsaId));
    }
}
```

### 3.2 Update `app/Models/DonationCampaign.php`
Add the expenses relationship and financial computation helpers:
```php
public function expenses()
{
    return $this->hasMany(MasjidExpense::class, 'campaign_id')->where('entry_type', 'expense');
}

public function getTotalExpensesAttribute()
{
    return (float) $this->expenses()->sum('amount');
}

public function getNetBalanceAttribute()
{
    $totalRaised = (float) $this->ledgers()->sum('paid_amount');
    return $totalRaised - $this->total_expenses;
}
```

---

## 4. API Endpoints Specification

All routes protected by `auth:sanctum`.

| Method | URI | Description | Parameters / Payload |
|---|---|---|---|
| `GET` | `/api/masjid-expenses` | List expenses and incomes with filters | `masjid_id`, `madarsa_id`, `campaign_id`, `entry_type`, `category`, `from_date`, `to_date`, `per_page` |
| `POST` | `/api/masjid-expenses` | Record a new expense or income | `masjid_id`, `campaign_id` (optional), `entry_type` (`expense`/`income`), `title`, `amount`, `category`, `payment_mode`, `transaction_date`, `vendor_name`, `receipt_file` (image upload), `notes` |
| `GET` | `/api/masjid-expenses/{id}` | Get single expense details | `id` |
| `PUT/PATCH` | `/api/masjid-expenses/{id}` | Update existing record | Form data (same as store) |
| `DELETE` | `/api/masjid-expenses/{id}` | Delete expense entry | Soft delete record |
| `GET` | `/api/masjid-expenses/summary` | Consolidated financial summary | `masjid_id`, `madarsa_id`, `campaign_id` (optional) |

### Financial Summary Payload Format (`GET /api/masjid-expenses/summary`)
```json
{
  "success": true,
  "data": {
    "total_campaign_collected": 350000.00,
    "total_general_income": 45000.00,
    "total_income": 395000.00,
    "total_campaign_expenses": 120000.00,
    "total_general_expenses": 35000.00,
    "total_expenses": 155000.00,
    "net_operating_balance": 240000.00,
    "category_breakdown": [
      {"category": "construction", "total": 120000.00, "count": 4},
      {"category": "utilities", "total": 18000.00, "count": 2},
      {"category": "salary", "total": 17000.00, "count": 3}
    ],
    "campaign_breakdowns": [
      {
        "campaign_id": 1,
        "campaign_name": "Minaret Renovation",
        "collected": 200000.00,
        "spent": 120000.00,
        "balance": 80000.00
      }
    ]
  }
}
```

---

## 5. Controller Implementation: `App\Http\Controllers\Api\MasjidExpenseController.php`

### Authorization Rules:
- Only **Mutawalli**, **Super Admin**, or authorized **Mohalla Mutawallis (Sub-Admins)** of that Masjid can record and view expenses.
- File uploads stored in `public/masjid_expenses/receipts`.
- Validations:
  - `amount`: `numeric|min:0.01`
  - `transaction_date`: `date|before_or_equal:today`
  - `receipt_file`: `nullable|image|max:5120` (5MB limit)

---

## 6. Implementation Steps Checklist
- [ ] Create migration `2026_10_22_000001_create_masjid_expenses_table.php` and run `php artisan migrate`.
- [ ] Create model `App\Models\MasjidExpense` with relationships and casts.
- [ ] Update `DonationCampaign.php` with `expenses()` relation.
- [ ] Create `App\Http\Controllers\Api\MasjidExpenseController.php`.
- [ ] Register routes in `routes/api.php` under Sanctum middleware.
- [ ] Test endpoints using PHPUnit / Postman / cURL.
