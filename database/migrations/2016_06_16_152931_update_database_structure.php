<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDatabaseStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hashtag', function (Blueprint $t) {
            $t->increments('id')->first();
        });

        Schema::rename('search_tag', 'account_has_hashtag');

        Schema::table('account_has_hashtag', function (Blueprint $t) {
            $t->renameColumn('hashtag', 'hashtag_id');
            $t->unsignedInteger('ad_account_id')->change();
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
            $t->removeColumn('create_account_id');
        });

        Schema::table('account_has_hashtag', function (Blueprint $t) {
            $t->unsignedInteger('hashtag_id')->change();
            $t->foreign('hashtag_id')->references('id')->on('hashtag');
        });

        Schema::table('post', function (Blueprint $t) {
            $t->bigIncrements('id')->first();
        });

        Schema::table('hashtag_has_post', function (Blueprint $t) {
            $t->renameColumn('hashtag', 'hashtag_id');
            $t->unsignedBigInteger('post_id')->change();
            $t->foreign('post_id')->references('id')->on('post');
        });

        Schema::table('hashtag_has_post', function (Blueprint $t) {
            $t->unsignedInteger('hashtag_id')->change();
            $t->foreign('hashtag_id')->references('id')->on('hashtag');
        });

        Schema::table('offer_target', function (Blueprint $t) {
            $t->unsignedInteger('offer_id')->change();
            $t->unsignedBigInteger('post_id')->change();
            $t->unsignedInteger('ad_account_id')->change();
            $t->unsignedInteger('create_account_id')->change();
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
            $t->foreign('create_account_id')->references('id')->on('account');
            $t->foreign('post_id')->references('id')->on('post');
            $t->foreign('offer_id')->references('id')->on('offer');
        });

        Schema::table('offer', function (Blueprint $t) {
            $t->unsignedInteger('ad_account_id')->change();
            $t->unsignedInteger('create_account_id')->change();
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
            $t->foreign('create_account_id')->references('id')->on('account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
