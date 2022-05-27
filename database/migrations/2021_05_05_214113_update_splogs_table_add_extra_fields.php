<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSplogsTableAddExtraFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('splogs', static function (Blueprint $table) {
            $table->dropColumn('article_length');
            $table->bigInteger('server_id', unsigned: true)->after('domain')->nullable();
            $table->integer('sections_from', unsigned: true)->after('server_id')->nullable();
            $table->integer('sections_to', unsigned: true)->after('sections_from')->nullable();
            $table->integer('words_from', unsigned: true)->after('sections_to')->nullable();
            $table->integer('words_to', unsigned: true)->after('words_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('splogs', static function (Blueprint $table) {
            $table->integer('article_length')->after('domain')->nullable();
            $table->dropColumn('server_id');
            $table->dropColumn('sections_from');
            $table->dropColumn('sections_to');
            $table->dropColumn('words_from');
            $table->dropColumn('words_to');
        });
    }
}
