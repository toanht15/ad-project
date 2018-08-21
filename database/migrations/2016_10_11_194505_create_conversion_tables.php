<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversion_types', function (Blueprint $t) {
            $t->increments('id');
            $t->string('conversion_type');
            $t->string('label');
            $t->timestamps();
        });

        Schema::create('conversion_values', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('conversion_type_id');
            $t->unsignedBigInteger('ad_insight_id');
            $t->integer('value');
            $t->timestamps();

            $t->foreign('conversion_type_id')->references('id')->on('conversion_types');
            $t->foreign('ad_insight_id')->references('id')->on('facebook_ads_insights');
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
