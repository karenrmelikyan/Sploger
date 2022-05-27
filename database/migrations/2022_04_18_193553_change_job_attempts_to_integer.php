<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeJobAttemptsToInteger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('jobs', static function (Blueprint $table) {
            $table->unsignedInteger('attempts')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('jobs', static function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->change();
        });
    }
}
