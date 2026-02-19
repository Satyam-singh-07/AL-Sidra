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
        Schema::table('madarsas', function (Blueprint $table) {

            $table->integer('students_count')->default(0)->after('status');
            $table->integer('staff_count')->default(0)->after('students_count');

            $table->string('contact_number')->after('staff_count');
            $table->string('alternate_contact')->nullable()->after('contact_number');
            $table->string('email')->nullable()->after('alternate_contact');
            $table->string('website_url')->nullable()->after('email');

            $table->string('registration_authority')->nullable()->after('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('madarsas', function (Blueprint $table) {
            //
        });
    }
};
