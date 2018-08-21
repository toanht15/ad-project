<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offers', function (Blueprint $t) {
            $t->dateTime('approved_at')->after('status');
        });

        Schema::create('approved_notifications', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('ad_account_id');
            $t->unsignedInteger('offer_set_group_id');
            $t->integer('new_approve_count');
            $t->dateTime('offered_time_sign');
            $t->boolean('is_read')->default(false);
            $t->timestamps();

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
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
