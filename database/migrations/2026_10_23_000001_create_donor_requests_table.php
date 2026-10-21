<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_requests', function (Blueprint $table) {
            $table->id();

            // Applicant User
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Target Masjid (or Madarsa)
            $table->foreignId('masjid_id')->nullable()->constrained('masjids')->onDelete('cascade');
            $table->foreignId('madarsa_id')->nullable()->constrained('madarsas')->onDelete('cascade');

            // Required Community Fields
            $table->string('applicant_name');          // Pre-filled from user, editable by Mutawalli
            $table->string('father_name');             // Father's name of the applicant (Required)
            $table->unsignedInteger('unit_count')->default(1); // Number of family members / units
            $table->string('phone')->nullable();       // Contact phone number

            // Mohalla Assignment (Assigned by Mutawalli upon acceptance)
            $table->foreignId('mohalla_id')->nullable()->constrained('mohallas')->nullOnDelete();
            $table->string('mohalla')->nullable();     // Cached Mohalla name

            // Workflow Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Review Details
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();             // Optional notes from user or Mutawalli
            $table->text('rejection_reason')->nullable();  // Reason if rejected

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['masjid_id', 'status']);
            $table->index(['user_id', 'masjid_id', 'status']);
            $table->index(['madarsa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_requests');
    }
};
