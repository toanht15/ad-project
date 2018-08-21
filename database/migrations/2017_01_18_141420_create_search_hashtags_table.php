<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchHashtagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_hashtags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('hashtag_id');
            $table->unsignedInteger('search_condition_id');
            $table->foreign('hashtag_id')->references('id')->on('hashtags');
            $table->foreign('search_condition_id')->references('id')->on('search_conditions');
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
        Schema::drop('search_hashtags');
    }
}
