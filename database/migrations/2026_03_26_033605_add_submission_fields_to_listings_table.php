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
        Schema::table('listings', function (Blueprint $table) {
            $table->string('submission_status', 20)->default('approved')->after('municipality');
            $table->string('local_connection_answer', 20)->nullable()->after('longitude');
            $table->string('independent_operation_answer', 20)->nullable()->after('local_connection_answer');
            $table->string('parent_affiliation_answer', 20)->nullable()->after('independent_operation_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'submission_status',
                'local_connection_answer',
                'independent_operation_answer',
                'parent_affiliation_answer',
            ]);
        });
    }
};