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
        Schema::table('timevault_objects', function (Blueprint $table) {
            // add unique index to the key column
            if (Schema::hasColumn('timevault_objects', 'key')) {
                $table->index('key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timevault_objects', function (Blueprint $table) {
            // drop unique index from the key column
            if (Schema::hasColumn('timevault_objects', 'key')) {
                $table->dropIndex(['key']);
            }
        });
    }
};
