<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }

            if (! Schema::hasColumn('suppliers', 'whatsapp_phone')) {
                $table->string('whatsapp_phone')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('suppliers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'company_name')) {
                $table->dropColumn('company_name');
            }

            if (Schema::hasColumn('suppliers', 'whatsapp_phone')) {
                $table->dropColumn('whatsapp_phone');
            }

            if (Schema::hasColumn('suppliers', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('suppliers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
