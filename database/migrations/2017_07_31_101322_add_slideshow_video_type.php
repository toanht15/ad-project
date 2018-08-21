<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlideshowVideoType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slideshows', function(Blueprint $t) {
            $t->tinyInteger('video_type')->after('time_per_img');
        });

        Schema::table('images', function(Blueprint $t) {
            $t->integer('height')->after('ad_account_id');
            $t->integer('width')->after('ad_account_id');
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
