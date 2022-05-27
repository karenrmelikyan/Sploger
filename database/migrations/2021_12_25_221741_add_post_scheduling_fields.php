<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostSchedulingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->tinyInteger('schedule_interval', unsigned: true)->after('keyword_density')->nullable();
            $table->tinyInteger('schedule_variance', unsigned: true)->after('schedule_interval')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropColumn('schedule_interval');
            $table->dropColumn('schedule_variance');
        });
    }
}
