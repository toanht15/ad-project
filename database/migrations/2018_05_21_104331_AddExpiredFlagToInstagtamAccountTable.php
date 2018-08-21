<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiredFlagToInstagtamAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->tinyInteger('expired_token_flg')->after('access_token')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->dropColumn('expired_token_flg');
        });
    }
}
