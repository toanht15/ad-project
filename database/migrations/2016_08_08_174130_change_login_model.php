<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLoginModel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_has_adaccount', function (Blueprint $t) {
            $t->dropRememberToken();
        });

        Schema::table('account', function (Blueprint $t) {
            $t->rememberToken()->after('status');
        });

        Schema::table('ad_account', function (Blueprint $t) {
            $t->rememberToken()->after('last_crawled_ad_id');
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
