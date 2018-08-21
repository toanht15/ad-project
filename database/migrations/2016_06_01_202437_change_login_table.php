<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account', function (Blueprint $t) {
            $t->dropColumn('email');
            $t->dropColumn('password');
            $t->dropColumn('remember_token');
        });

        Schema::table('account_has_adaccount', function (Blueprint $t) {
            $t->string('remember_token');
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
