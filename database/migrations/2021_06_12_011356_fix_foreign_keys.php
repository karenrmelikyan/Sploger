<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('articles', static function (Blueprint $table) {
            $table->dropForeign('articles_keyword_id_foreign');
            $table
                ->foreign('keyword_id')
                ->references('id')
                ->on('keywords')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('markov_matrix', static function (Blueprint $table) {
            $table
                ->foreign('keyword_id')
                ->references('id')
                ->on('keywords')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('articles', static function (Blueprint $table) {
            $table->dropForeign('articles_keyword_id_foreign');
            $table
                ->foreign('keyword_id')
                ->references('id')
                ->on('keywords')
                ->onDelete('cascade');
        });

        Schema::table('markov_matrix', static function (Blueprint $table) {
            $table->dropForeign('markov_matrix_keyword_id_foreign');
        });
    }
}
