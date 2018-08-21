<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAndChangeInviteCodeType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_codes', function (Blueprint $t) {
            $t->string('type', 25)->after('code');
            $t->unsignedInteger('created_admin_id')->nullable()->after('code');
            $t->string('invite_email')->after('code');
            $t->foreign('created_admin_id')->references('id')->on('admins');
        });
        Schema::rename('admin_codes', 'invite_codes');

        Schema::table('ad_account', function (Blueprint $t) {
            $t->unsignedInteger('code_id')->nullable()->after('last_crawled_ad_id');
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
        Schema::table('invite_codes', function (Blueprint $t) {
            $t->dropForeign('admin_codes_created_admin_id_foreign');
            $t->dropColumn('type');
            $t->dropColumn('created_admin_id');
            $t->dropColumn('invite_email');
        });
        Schema::rename('invite_codes', 'admin_codes');
    }
}
