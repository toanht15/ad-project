<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePredictionEngineDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hashtag_categories', function(Blueprint $t) {
            $t->increments('id');
            $t->string('category_name_en');
            $t->string('category_name_jp');
            $t->timestamps();
        });

        Schema::create('hashtag_has_categories', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('hashtag_category_id');
            $t->unsignedInteger('hashtag_id');
            $t->timestamps();
            $t->foreign('hashtag_category_id')->references('id')->on('hashtag_categories');
            $t->foreign('hashtag_id')->references('id')->on('hashtags');
        });

        Schema::create('advertiser_likes', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('search_condition_id');
            $t->unsignedBigInteger('post_id');
            $t->unsignedInteger('ad_account_id');
            $t->boolean('is_like')->default(true);
            $t->timestamps();
            $t->foreign('search_condition_id')->references('id')->on('search_conditions');
            $t->foreign('post_id')->references('id')->on('posts');
            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
        });

        Schema::create('score_types', function(Blueprint $t) {
            $t->increments('id');
            $t->string('score_type');
            $t->integer('reliability_score');
            $t->timestamps();
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

        Schema::table('search_conditions', function(Blueprint $t) {
            $t->tinyInteger('inspiration_status')->after('ad_account_id');
            $t->tinyInteger('score_status')->after('ad_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('image_scores');
        Schema::drop('img_score_details');
        Schema::drop('score_types');
        Schema::drop('advertiser_likes');
        Schema::drop('hashtag_has_categories');
        Schema::drop('hashtag_categories');
        Schema::table('search_conditions', function(Blueprint $t) {
            $t->dropColumn('inspiration_status');
            $t->dropColumn('score_status');
        });
    }
}
