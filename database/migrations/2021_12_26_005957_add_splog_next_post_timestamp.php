<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSplogNextPostTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('splogs', static function (Blueprint $table) {
            $table->timestamp('next_post_at')->nullable()->after('instance_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('splogs', static function (Blueprint $table) {
            $table->dropColumn('next_post_at');
        });
    }
}
