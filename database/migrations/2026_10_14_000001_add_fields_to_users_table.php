<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer')->after('password');          // admin | customer
            $t->string('account_type')->default('B2C')->after('role');           // B2C | B2B
            $t->string('company_name')->nullable()->after('account_type');
            $t->string('phone')->nullable()->after('company_name');
            $t->text('address')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['role', 'account_type', 'company_name', 'phone', 'address']);
        });
    }
};
