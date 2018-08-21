<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('image_scores');
        Schema::drop('img_score_details');
        Schema::drop('score_types');
        Schema::drop('advertiser_likes');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::create('score_types', function(Blueprint $t) {
            $t->increments('id');
            $t->string('score_type');
            $t->integer('reliability_score');
            $t->string('lib_path');
            $t->text('features');
            $t->double('standard', 8, 2);
            $t->timestamps();
        });

        Schema::create('image_scores', function(Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('post_id');
            $t->unsignedInteger('search_condition_id');
            $t->tinyInteger('status');
            $t->integer('score');
            $t->timestamps();
            $t->foreign('post_id')->references('id')->on('posts');
            $t->foreign('search_condition_id')->references('id')->on('search_conditions');
        });

        Schema::create('img_score_details', function(Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('post_id');
            $t->unsignedInteger('search_condition_id');
            $t->unsignedInteger('score_type_id');
            $t->integer('score_detail');
            $t->timestamps();
            $t->foreign('post_id')->references('id')->on('posts');
            $t->foreign('search_condition_id')->references('id')->on('search_conditions');
            $t->foreign('score_type_id')->references('id')->on('score_types');
        });

        Schema::create('advertiser_likes', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('search_condition_id');
            $t->unsignedBigInteger('post_id');
            $t->unsignedInteger('advertiser_id');
            $t->boolean('is_like')->default(true);
            $t->timestamps();
            $t->unsignedInteger('account_id');
            $t->foreign('search_condition_id')->references('id')->on('search_conditions');
            $t->foreign('post_id')->references('id')->on('posts');
            $t->foreign('advertiser_id')->references('id')->on('advertisers');
        });
    }
}
