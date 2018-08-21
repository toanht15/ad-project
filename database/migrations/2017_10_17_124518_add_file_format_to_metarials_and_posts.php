<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileFormatToMetarialsAndPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function($table) {
            $table->integer('file_format')->after('ad_account_id')->default(1);
            $table->text('video_url')->after('image_url')->nullable();
        });

        Schema::table('posts', function($table) {
            $table->integer('file_format')->after('post_id')->default(1);
            $table->text('video_url')->after('post_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('images', function($table) {
            $table->dropColumn('file_format');
            $table->dropColumn('video_url');
        });

        Schema::table('posts', function($table) {
            $table->dropColumn('file_format');
            $table->dropColumn('video_url');
        });
    }
}
