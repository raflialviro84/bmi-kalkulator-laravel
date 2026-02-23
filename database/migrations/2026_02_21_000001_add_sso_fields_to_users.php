<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_id')->nullable()->unique()->after('id');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('gender')->nullable()->after('phone');
            $table->date('birthdate')->nullable()->after('gender');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null')->after('birthdate');
            $table->json('sso_raw')->nullable()->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_id', 'phone', 'gender', 'birthdate', 'sso_raw']);
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
