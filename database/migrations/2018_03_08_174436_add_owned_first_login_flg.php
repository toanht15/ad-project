<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOwnedFirstLoginFlg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('contract_services',function (Blueprint $table){
            $table->tinyInteger('is_owned_first')->after('max_media_account')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_services',function (Blueprint $table){
            $table->dropColumn('is_owned_first');
        });
    }
}
