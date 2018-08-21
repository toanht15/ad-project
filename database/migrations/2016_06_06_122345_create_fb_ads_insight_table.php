<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbAdsInsightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_ads_insights', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('ad_account_id');
            $t->unsignedInteger('facebook_ad_id');
            $t->date('date');
            $t->integer('click');
            $t->integer('spend');
            $t->integer('impression');
            $t->float('ctr', 10, 8);
            $t->timestamps();

            $t->foreign('ad_account_id')->references('id')->on('ad_account');
            $t->foreign('facebook_ad_id')->references('id')->on('facebook_ads');
        });

        Schema::table('facebook_ads', function (Blueprint $t) {
            $t->unsignedInteger('ad_account_id')->after('id');
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
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
