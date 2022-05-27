<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstanceStatusToSplogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('splogs', function (Blueprint $table) {
            $table->tinyInteger('instance_status')
                ->default(0)
                ->after('database_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('splogs', function (Blueprint $table) {
            $table->dropColumn('instance_status');
        });
    }
}
