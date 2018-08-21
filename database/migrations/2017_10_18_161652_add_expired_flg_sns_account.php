<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiredFlgSnsAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sns_accounts', function(Blueprint $t) {
            $t->boolean('token_expired_flg')->after('expired_at');
        });

        Schema::table('invitation_codes', function(Blueprint $t) {
            $t->string('code')->after('created_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sns_accounts', function(Blueprint $t) {
            $t->dropColumn('token_expired_flg');
        });

        Schema::table('invitation_codes', function(Blueprint $t) {
            $t->dropColumn('code');
        });
    }
}
