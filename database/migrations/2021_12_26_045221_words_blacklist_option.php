<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WordsBlacklistOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('settings')->where([
            'name' => 'blacklist',
        ])->update(['name' => 'url_blacklist']);
        DB::table('settings')->insert([
            'name' => 'words_blacklist',
            'value' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('settings')->where([
            'name' => 'words_blacklist',
        ])->delete();
        DB::table('settings')->where([
            'name' => 'url_blacklist',
        ])->update(['name' => 'blacklist']);
    }
}
