<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveAdaccountCodeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ad_account', function (Blueprint $t) {
            $t->dropForeign('ad_account_code_id_foreign');
            $t->dropColumn('code_id');
        });

        Schema::table('account', function (Blueprint $t) {
            $t->unsignedInteger('code_id')->nullable()->after('id');
            $t->foreign('code_id')->references('id')->on('invite_codes');
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
