<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlideshowDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slideshows', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('ad_account_id');
            $t->string('name');
            $t->tinyInteger('status');
            $t->string('fb_video_id');
            $t->integer('time_per_img');
            $t->tinyInteger('effect_type');
            $t->timestamps();
            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
        });

        Schema::create('slideshow_images', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('slideshow_id');
            $t->unsignedInteger('image_id');
            $t->timestamps();
            $t->foreign('slideshow_id')->references('id')->on('slideshows');
            $t->foreign('image_id')->references('id')->on('images');
        });

        Schema::create('ads_use_slideshows', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('ad_id');
            $t->unsignedInteger('slideshow_id');
            $t->timestamps();
            $t->foreign('ad_id')->references('id')->on('facebook_ads');
            $t->foreign('slideshow_id')->references('id')->on('slideshows');
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
