<?php

declare(strict_types=1);

use App\Enums\CacheStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorAddCacheStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Remove caches
        DB::table('articles')->delete();
        DB::table('markov_matrix')->delete();

        /**
         * @var array<string, int[]> $keywordLanguages
         */
        $keywordLanguages = DB::table('keywords')
            ->select(['keywords.id', 'projects.language'])
            ->join('keyword_set', 'keyword_set.keyword_id', '=', 'keywords.id')
            ->join('keyword_sets', 'keyword_set.set_id', '=', 'keyword_sets.id')
            ->join('projects', 'projects.keyword_set_id', '=', 'keyword_sets.id')
            ->distinct()
            ->get()
            ->mapToGroups(static fn($item) => [((array)$item)['language'] => ((array)$item)['id']])
            ->toArray();

        // Add language code to keywords
        Schema::table('keywords', static function (Blueprint $table) {
            $table->char('language_code', 2)->after('id')->default('en');
            $table->dropUnique(['name']);
            $table->unique(['name', 'language_code']);
        });

        // Update keywords to add language there for migration purposes
        foreach ($keywordLanguages as $language => $keywords) {
            DB::table('keywords')->whereIn('id', $keywords)->update(['language_code' => $language]);
        }

        // Drop language from projects
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropColumn('language');
        });

        // Remove language_code from articles
        Schema::table('articles', static function (Blueprint $table) {
            $table->dropColumn('language_code');
        });

        // Remove language_code from matrix
        Schema::table('markov_matrix', static function (Blueprint $table) {
            $table->dropForeign(['keyword_id']);
            $table->dropPrimary(['keyword_id', 'language_code']);
            $table->dropColumn('language_code');
            $table->primary('keyword_id');
            $table
                ->foreign('keyword_id')
                ->references('id')
                ->on('keywords')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // Add cache statuses to keywords
        Schema::table('keywords', static function (Blueprint $table) {
            $table
                ->tinyInteger('article_cache_status', unsigned: true)
                ->after('name')
                ->default(CacheStatus::EMPTY->value);
            $table
                ->tinyInteger('markov_cache_status', unsigned: true)
                ->after('article_cache_status')
                ->default(CacheStatus::EMPTY->value);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Add language code to projects
        Schema::table('projects', static function (Blueprint $table) {
            $table->char('language', 2)->after('id');
        });

        /**
         * @var array<string, int[]> $projectLanguages
         */
        $projectLanguages = DB::table('projects')
            ->select(['projects.id', 'keywords.language_code'])
            ->join('keyword_sets', 'projects.keyword_set_id', '=', 'keyword_sets.id')
            ->join('keyword_set', 'keyword_set.set_id', '=', 'keyword_sets.id')
            ->join('keywords', 'keyword_set.keyword_id', '=', 'keywords.id')
            ->distinct()
            ->get()
            ->mapToGroups(static fn($item) => [((array)$item)['language_code'] => ((array)$item)['id']])
            ->toArray();

        // Update projects to add language there for migration purposes
        foreach ($projectLanguages as $language => $projects) {
            DB::table('projects')->whereIn('id', $projects)->update(['language' => $language]);
        }

        // Drop language from keywords
        Schema::table('keywords', static function (Blueprint $table) {
            $table->dropColumn('language_code');
            $table->dropUnique(['name', 'language_code']);
            $table->unique('name');
        });

        // Add language_code to articles
        Schema::table('articles', static function (Blueprint $table) {
            $table->char('language_code', 2);
        });

        // Add language_code to matrix
        Schema::table('markov_matrix', static function (Blueprint $table) {
            $table->dropForeign(['keyword_id']);
            $table->dropPrimary('keyword_id');
            $table->char('language_code', 2);
            $table->primary(['keyword_id', 'language_code']);
            $table
                ->foreign('keyword_id')
                ->references('id')
                ->on('keywords')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // Remove cache statuses from keywords
        Schema::table('keywords', static function (Blueprint $table) {
            $table->dropColumn('article_cache_status');
            $table->dropColumn('markov_cache_status');
        });
    }
}
