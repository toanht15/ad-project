<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAdAccountForiegnkey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ad_account', function (Blueprint $t) {
            $t->unsignedInteger('instagram_account_id')->nullable()->change();
            $t->dropUnique('ad_account_instagram_account_id_unique');
            $t->foreign('instagram_account_id')->references('id')->on('instagram_account');
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
