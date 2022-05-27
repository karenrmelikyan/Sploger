<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMarkovMatrixTableAddQuality extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markov_matrix', function (Blueprint $table) {
            $table->integer('tokens')->nullable()->after('matrix');
            $table->integer('distinct_tokens')->nullable()->after('tokens');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markov_matrix', function (Blueprint $table) {
            $table->dropColumn('tokens');
            $table->dropColumn('distinct_tokens');
        });
    }
}
