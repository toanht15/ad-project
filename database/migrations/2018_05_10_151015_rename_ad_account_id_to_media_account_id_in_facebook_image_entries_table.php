<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAdAccountIdToMediaAccountIdInFacebookImageEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_image_entries', function ($table) {
            $table->dropForeign(['ad_account_id']);
            $table->dropIndex('facebook_image_entries_ad_account_id_foreign');
            $table->renameColumn('ad_account_id', 'media_account_id');
            $table->foreign('media_account_id')->references('id')->on('media_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_image_entries', function ($table) {
            $table->dropForeign(['media_account_id']);
            $table->renameColumn('media_account_id', 'ad_account_id');
            $table->foreign('ad_account_id')->references('id')->on('media_accounts');
        });
    }
}
