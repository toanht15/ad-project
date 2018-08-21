<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsUseImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads_use_images', function (Blueprint $t) {
            $t->unsignedInteger('image_entry_id');
            $t->unsignedInteger('ad_id');
            $t->timestamps();
            $t->foreign('image_entry_id')->references('id')->on('facebook_image_entries');
            $t->foreign('ad_id')->references('id')->on('facebook_ads');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ads_use_images');
    }
}
