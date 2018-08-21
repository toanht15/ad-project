<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAccountIdToUserIdInapprovedNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approved_notifications', function ($table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex('approved_notifications_account_id_foreign');
            $table->renameColumn('account_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approved_notifications', function ($table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'account_id');
            $table->foreign('account_id')->references('id')->on('users');
        });
    }
}
