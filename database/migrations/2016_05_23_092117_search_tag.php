<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SearchTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //クロール用検索タグ
        Schema::create('search_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hashtag');
            $table->integer('ad_account_id')->index();
            $table->integer('create_account_id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."search_tag CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."search_tag COMMENT 'crowing searh hashtag'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('search_tag');
    }
}
