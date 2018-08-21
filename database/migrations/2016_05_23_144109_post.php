<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Post extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //PMから取得したデータの一時保管テーブル
        Schema::create('post', function (Blueprint $table) {
            $table->string('post_id');
            $table->text('image_url');
            $table->text('post_url');
            $table->text('author_url');
            $table->text('author_name');
            $table->text('author_icon_img');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."post CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."post COMMENT 'PMから取得したデータの一時保管テーブル'");

        //ハッシュタグ 定期的にsearch_tagからhashtagを取り出して登録する?
        Schema::create('hashtag', function (Blueprint $table) {
            $table->string('hashtag')->unique();
            $table->integer('post_count'); //集計結果
            $table->integer('post_count_last_days'); //集計結果前日
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."hashtag CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."hashtag COMMENT 'PMから取得したデータの一時保管テーブル'");

        //ハッシュタグとpostの紐付け
        Schema::create('hashtag_has_post', function (Blueprint $table) {
            $table->string('hashtag');
            $table->string('post_id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."hashtag_has_post CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."hashtag_has_post COMMENT 'ハッシュタグとの紐付け'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('post');
        Schema::drop('hashtag');
        Schema::drop('hashtag_has_post');
    }
}
