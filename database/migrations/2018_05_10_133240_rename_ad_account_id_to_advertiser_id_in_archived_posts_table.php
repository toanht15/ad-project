<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAdAccountIdToAdvertiserIdInArchivedPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archived_posts', function ($table) {
            $table->dropForeign(['ad_account_id']);
            $table->dropIndex('archived_posts_ad_account_id_foreign');
            $table->renameColumn('ad_account_id', 'advertiser_id');
            $table->foreign('advertiser_id')->references('id')->on('advertisers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archived_posts', function ($table) {
            $table->dropForeign(['advertiser_id']);
            $table->renameColumn('advertiser_id', 'ad_account_id');
            $table->foreign('ad_account_id')->references('id')->on('advertisers');
        });
    }
}
