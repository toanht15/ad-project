<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupportTwDatabaseChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $t) {
            $t->increments('id');
            $t->string('email');
            $t->string('user_name');
            $t->string('profile_img_url');
            $t->unsignedInteger('tenant_id');
            $t->string('role', 50);
            $t->softDeletes();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('sns_accounts', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->string('media_user_id');
            $t->string('name');
            $t->string('profile_img_url');
            $t->text('access_token');
            $t->text('refresh_token');
            $t->dateTime('expired_at');
            $t->tinyInteger('media_type');
            $t->softDeletes();
            $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('advertisers', function(Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->unsignedInteger('tenant_id');
            $t->integer('max_search_condition')->default(15);
            $t->boolean('completed_tutorial_flg');
            $t->softDeletes();
            $t->timestamps();
            $t->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('user_advertisers', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('advertiser_id');
            $t->unsignedInteger('user_id');
            $t->string('role', 50);
            $t->timestamps();
            $t->foreign('advertiser_id')->references('id')->on('advertisers');
            $t->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('invitation_codes', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->string('invited_email');
            $t->integer('created_user_id');
            $t->dateTime('expired_date');
            $t->boolean('is_used_flg');
            $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('media_tokens', function(Blueprint $t) {
            $t->increments('id');
            $t->string('media_account_id');
            $t->tinyInteger('media_type');
            $t->text('access_token');
            $t->text('refresh_token');
            $t->boolean('token_expired_flg');
            $t->timestamps();
        });

        Schema::create('media_accounts', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('media_token_id');
            $t->unsignedInteger('advertiser_id');
            $t->tinyInteger('media_type');
            $t->string('media_account_id');
            $t->string('name');
            $t->string('last_crawled_ad_id');
            $t->timestamps();
            $t->foreign('media_token_id')->references('id')->on('media_tokens');
            $t->foreign('advertiser_id')->references('id')->on('advertisers');
        });

        Schema::create('advertiser_instagram_accounts', function(Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('advertiser_id');
            $t->unsignedInteger('instagram_account_id');
            $t->timestamps();
            $t->foreign('advertiser_id')->references('id')->on('advertisers');
            $t->foreign('instagram_account_id')->references('id')->on('instagram_accounts');
        });

//        Schema::rename('facebook_ads_actions', 'ad_conversions');
//
//        Schema::rename('facebook_actions', 'conversion_types');
//
//        Schema::rename('images', 'materials');
//
//        Schema::rename('facebook_image_entries', 'material_media_entries');
//
//        Schema::rename('ads_use_images', 'ads_use_materials');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::rename('ads_use_materials', 'ads_use_images');
//        Schema::rename('material_media_entries', 'facebook_image_entries');
//        Schema::rename('materials', 'images');
//        Schema::rename('conversion_types', 'facebook_actions');
//        Schema::rename('ad_conversions', 'facebook_ads_actions');

        Schema::drop('sns_accounts');
        Schema::drop('user_advertisers');
        Schema::drop('invitation_codes');
        Schema::drop('users');
        Schema::drop('media_accounts');
        Schema::drop('advertiser_instagram_accounts');
        Schema::drop('advertisers');
        Schema::drop('media_tokens');
    }
}
