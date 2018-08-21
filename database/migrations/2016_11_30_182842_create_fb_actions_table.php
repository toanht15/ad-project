<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_actions', function (Blueprint $t) {
            $t->increments('id');
            $t->string('action_type');
            $t->string('label');
            $t->timestamps();
        });

        Schema::create('facebook_ads_actions', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('ad_account_id');
            $t->unsignedInteger('facebook_ad_id');
            $t->unsignedBigInteger('facebook_ads_insight_id');
            $t->unsignedInteger('facebook_action_id');
            $t->date('date');
            $t->integer('value');
            $t->timestamps();

            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
            $t->foreign('facebook_ad_id')->references('id')->on('facebook_ads');
            $t->foreign('facebook_ads_insight_id')->references('id')->on('facebook_ads_insights');
            $t->foreign('facebook_action_id')->references('id')->on('facebook_actions');
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
