<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAdaccountForiegnKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function(Blueprint $t) {
            $t->dropForeign('tenants_created_admin_id_foreign');
            $t->foreign('created_admin_id')->references('id')->on('users');
        });

        Schema::table('advertiser_likes', function(Blueprint $t) {
            $t->dropForeign('advertiser_likes_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
        });

        Schema::table('approved_notifications', function(Blueprint $t) {
            $t->dropForeign('approved_notifications_ad_account_id_foreign');
            $t->dropForeign('approved_notifications_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
            $t->foreign('account_id')->references('id')->on('users');
        });

        Schema::table('archived_posts', function(Blueprint $t) {
            $t->dropForeign('archived_posts_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
        });

        Schema::table('facebook_ad_account_insights', function(Blueprint $t) {
            $t->dropForeign('facebook_ad_account_insights_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('media_accounts');
        });

        Schema::table('facebook_ads', function(Blueprint $t) {
            $t->dropForeign('facebook_ads_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('media_accounts');
        });

        Schema::table('facebook_ads_actions', function(Blueprint $t) {
            $t->dropForeign('facebook_ads_actions_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('media_accounts');
        });

        Schema::table('facebook_ads_insights', function(Blueprint $t) {
            $t->dropForeign('facebook_ads_insights_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('media_accounts');
        });

        Schema::table('images', function(Blueprint $t) {
            $t->dropForeign('images_ad_account_id_foreign');
            $t->dropForeign('images_create_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
            $t->foreign('create_account_id')->references('id')->on('users');
        });

        Schema::table('offer_set_groups', function(Blueprint $t) {
            $t->dropForeign('offer_set_groups_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
        });

        Schema::table('offer_sets', function(Blueprint $t) {
            $t->dropForeign('offer_ad_account_id_foreign');
            $t->dropForeign('offer_create_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
            $t->foreign('create_account_id')->references('id')->on('users');
        });

        Schema::table('offers', function(Blueprint $t) {
            $t->dropForeign('offer_target_ad_account_id_foreign');
            $t->dropForeign('offer_target_create_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
            $t->foreign('create_account_id')->references('id')->on('users');
        });

        Schema::table('search_conditions', function(Blueprint $t) {
            $t->dropForeign('search_conditions_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
        });

        Schema::table('slideshows', function(Blueprint $t) {
            $t->dropForeign('slideshows_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('advertisers');
        });

        Schema::table('facebook_image_entries', function(Blueprint $t) {
            $t->dropForeign('facebook_image_entries_ad_account_id_foreign');
            $t->foreign('ad_account_id')->references('id')->on('media_accounts');
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
