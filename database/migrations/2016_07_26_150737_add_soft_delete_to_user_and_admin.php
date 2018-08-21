<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteToUserAndAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account', function (Blueprint $t) {
            $t->softDeletes()->after('status');
        });

        Schema::table('ad_account', function (Blueprint $t) {
            $t->softDeletes()->after('last_crawled_ad_id');
        });

        Schema::table('admins', function (Blueprint $t) {
            $t->softDeletes()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account', function (Blueprint $t) {
            $t->dropSoftDeletes();
        });

        Schema::table('ad_account', function (Blueprint $t) {
            $t->dropSoftDeletes();
        });

        Schema::table('admins', function (Blueprint $t) {
            $t->dropSoftDeletes();
        });
    }
}
