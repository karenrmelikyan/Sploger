<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkovMatrixTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markov_matrix', function (Blueprint $table) {
            $table->unsignedBigInteger('keyword_id');
            $table->char('language_code', 2);
            $table->longText('matrix');
            $table->primary(['keyword_id', 'language_code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('markov_matrix');
    }
}
