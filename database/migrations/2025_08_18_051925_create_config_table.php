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
       if ( !Schema::hasTable('configs') ) {
            Schema::create('configs', function (Blueprint $table) {
                $table->id();
                $table->string('config', 64)->comment('Name of config');
                $table->string('key', 128)->comment('Key of config');
                $table->json('value')->nullable()->comment('value of config');
                $table->timestamps();

                $table->unique(['config', 'key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropUnique(['config', 'key']);
        });

        Schema::dropIfExists('configs');
    }
};
