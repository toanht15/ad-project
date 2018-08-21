<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbAdAccountInsightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_ad_account_insights', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('ad_account_id');
            $table->date('date');
            $table->integer('spend');
            $table->timestamps();

            $table->foreign('ad_account_id')->references('id')->on('ad_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('facebook_ad_account_insights');
    }
}
