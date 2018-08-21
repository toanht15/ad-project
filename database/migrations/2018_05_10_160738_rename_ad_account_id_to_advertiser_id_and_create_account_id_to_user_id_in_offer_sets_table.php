<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAdAccountIdToAdvertiserIdAndCreateAccountIdToUserIdInOfferSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offers', function ($table) {
            $table->dropForeign(['ad_account_id']);
            $table->dropIndex('offer_target_ad_account_id_foreign');
            $table->renameColumn('ad_account_id', 'advertiser_id');
            $table->foreign('advertiser_id')->references('id')->on('advertisers');

            $table->dropForeign(['create_account_id']);
            $table->dropIndex('offer_target_create_account_id_foreign');
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
        Schema::table('offers', function ($table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('offers_user_id_foreign');
            $table->renameColumn('user_id', 'create_account_id');
            $table->foreign('create_account_id')->references('id')->on('users');

            $table->dropForeign(['advertiser_id']);
            $table->dropIndex('offers_advertiser_id_foreign');
            $table->renameColumn('advertiser_id', 'ad_account_id');
            $table->foreign('ad_account_id')->references('id')->on('advertisers');

            $table->index('ad_account_id', 'offer_target_ad_account_id_foreign');
            $table->index('create_account_id', 'offer_target_create_account_id_foreign');
        });
    }
}
