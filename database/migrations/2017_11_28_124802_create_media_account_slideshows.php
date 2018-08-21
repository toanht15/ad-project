<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaAccountSlideshows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ads_use_slideshows', function(Blueprint $t) {
            $t->dropForeign('ads_use_slideshows_slideshow_id_foreign');
        });
        Schema::table('facebook_image_entries', function(Blueprint $t) {
            $t->string('text')->after('creative_type');
        });
        Schema::create('media_account_slideshows', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('media_account_id');
            $t->unsignedInteger('slideshow_id');
            $t->string('media_object_id');
            $t->string('text');
            $t->string('creative_type');
            $t->timestamps();
            $t->foreign('media_account_id')->references('id')->on('media_accounts');
            $t->foreign('slideshow_id')->references('id')->on('slideshows');
        });
        Schema::table('ads_use_slideshows', function(Blueprint $t) {
            $t->renameColumn('slideshow_id', 'media_slideshow_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
