<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNameOfTableWithFacebookAttached extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::rename('facebook_actions', 'conversion_types');
        Schema::rename('facebook_ad_account_insights', 'media_ad_account_insights');
        Schema::rename('facebook_ads', 'media_ads');
        Schema::rename('facebook_ads_actions', 'ads_conversions');
        Schema::rename('facebook_ads_insights', 'media_ads_insights');
        Schema::rename('facebook_image_entries', 'media_image_entries');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::rename('conversion_types', 'facebook_actions');
        Schema::rename('media_ad_account_insights', 'facebook_ad_account_insights');
        Schema::rename('media_ads', 'facebook_ads');
        Schema::rename('ads_conversions', 'facebook_ads_actions');
        Schema::rename('media_ads_insights', 'facebook_ads_insights');
        Schema::rename('media_image_entries', 'facebook_image_entries');
    }
}
