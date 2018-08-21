<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLoginDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account', function (Blueprint $t) {
            $t->dropColumn('ad_account_id');
        });

        Schema::create('account_has_adaccount', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('ad_account_id');
            $t->timestamps();
            $t->foreign('account_id')->references('id')->on('account');
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
        Schema::drop('account_has_adaccount');
    }
}
