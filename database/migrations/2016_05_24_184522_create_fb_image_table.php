<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_image_entries', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('offer_target_id');
            $t->unsignedInteger('ad_account_id');
            $t->tinyInteger('status')->default(0);
            $t->string('img_url');
            $t->string('hash_code');
            $t->timestamps();
            $t->foreign('offer_target_id')->references('id')->on('offer_target');
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
        });

        Schema::table('ad_account', function (Blueprint $t) {
            $t->string('last_crawled_ad_id')->after('business_name')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facebook_image_entries');
        Schema::table('ad_account', function (Blueprint $t) {
            $t->dropColumn('last_crawled_ad_id');
        });
    }
}
