<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferSetGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_set_groups', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('ad_account_id');
            $t->string('title');
            $t->integer('approved_image_count');
            $t->integer('offering_image_count');
            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
            $t->timestamps();
        });

        Schema::table('offer_sets', function (Blueprint $t) {
            $t->unsignedInteger('offer_set_group_id')->nullable()->after('id');
            $t->foreign('offer_set_group_id')->references('id')->on('offer_set_groups');
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
