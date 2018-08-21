<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnToHashtagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hashtags', function (Blueprint $table) {
            $table->renameColumn('sync_flg', 'active_flg');
            $table->dropColumn('post_count_last_days');
            $table->string('next_max_tag_id', 255)->after('hashtag');
            $table->timestamp('last_crawled_at')->after('hashtag');
            $table->bigInteger('hashtag_code')->after('hashtag');
            $table->index('hashtag_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hashtags', function (Blueprint $table) {
            $table->renameColumn('active_flg', 'sync_flg');
            $table->dropColumn('hashtag_code');
            $table->dropColumn('next_max_tag_id');
            $table->dropColumn('last_crawled_at');
            $table->integer('post_count');
            $table->integer('post_count_last_days');
        });
    }
}
