<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProjectsTableAddExtraFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->integer('sections_from', unsigned: true)->after('keyword_set_id')->default(3);
            $table->integer('sections_to', unsigned: true)->after('sections_from')->default(6);
            $table->integer('words_from', unsigned: true)->after('sections_to')->default(150);
            $table->integer('words_to', unsigned: true)->after('words_from')->default(300);
            $table->tinyInteger('keyword_density', unsigned: true)->after('words_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropColumn('sections_from');
            $table->dropColumn('sections_to');
            $table->dropColumn('words_from');
            $table->dropColumn('words_to');
            $table->dropColumn('keyword_density');
        });
    }
}
