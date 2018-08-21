<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditHashtagsTablesDefaultLastCrawledAtColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE hashtags MODIFY last_crawled_at TIMESTAMP DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE hashtags MODIFY last_crawled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }
}
