<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAdAccountIdToAdvertiserIdAndCreatedAccountIdToUserIdInFacebookAdsInsightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function ($table) {
            $table->dropForeign(['ad_account_id']);
            $table->dropIndex('images_ad_account_id_foreign');
            $table->renameColumn('ad_account_id', 'advertiser_id');
            $table->foreign('advertiser_id')->references('id')->on('advertisers');

            $table->dropForeign(['create_account_id']);
            $table->dropIndex('images_create_account_id_foreign');
            $table->renameColumn('create_account_id', 'user_id');
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
        Schema::table('images', function ($table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'create_account_id');
            $table->foreign('create_account_id')->references('id')->on('users');

            $table->dropForeign(['advertiser_id']);
            $table->renameColumn('advertiser_id', 'ad_account_id');
            $table->foreign('ad_account_id')->references('id')->on('advertisers');
        });
    }
}
