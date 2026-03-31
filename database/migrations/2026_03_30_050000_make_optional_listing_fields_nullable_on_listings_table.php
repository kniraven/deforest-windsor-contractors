<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('other_service_type')->nullable()->change();
            $table->string('legal_structure')->nullable()->change();
            $table->string('other_legal_structure')->nullable()->change();

            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();

            $table->string('local_connection_answer')->nullable()->change();
            $table->string('independent_operation_answer')->nullable()->change();
            $table->string('parent_affiliation_answer')->nullable()->change();

            $table->string('street_address')->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('website_url')->nullable()->change();

            $table->text('internal_notes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('other_service_type')->nullable(false)->change();
            $table->string('legal_structure')->nullable(false)->change();
            $table->string('other_legal_structure')->nullable(false)->change();

            $table->decimal('latitude', 10, 7)->nullable(false)->change();
            $table->decimal('longitude', 10, 7)->nullable(false)->change();

            $table->string('local_connection_answer')->nullable(false)->change();
            $table->string('independent_operation_answer')->nullable(false)->change();
            $table->string('parent_affiliation_answer')->nullable(false)->change();

            $table->string('street_address')->nullable(false)->change();
            $table->string('postal_code', 20)->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('website_url')->nullable(false)->change();

            $table->text('internal_notes')->nullable(false)->change();
        });
    }
};