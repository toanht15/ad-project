<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUselessTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('ad_account_permissions');
        Schema::drop('account_has_adaccount');
        Schema::drop('ad_accounts');
        Schema::drop('accounts');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');   // not check foreign key when delete
        Schema::drop('invite_codes');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Schema::drop('admins');
        Schema::drop('announsement');
        Schema::drop('author_approveds');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('author_approveds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('ad_account_id');
            $table->unsignedInteger('author_id');
            $table->integer('approved');
            $table->timestamps();
        });

        Schema::create('announsement', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('body');
            $table->boolean('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code_id');
            $table->string('name');
            $table->string('facebook_id');
            $table->text('access_token');
            $table->string('profile_image');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('invite_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id')->nullable();
            $table->string('code');
            $table->string('invite_email');
            $table->unsignedInteger('created_admin_id')->nullable();
            $table->string('type', 25);
            $table->boolean('is_used');
            $table->dateTime('expire_date');
            $table->timestamps();
            $table->foreign('created_admin_id')->references('id')->on('admins');
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('code_id')->nullable();
            $table->unsignedInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->text('profile_image') ;
            $table->string('facebook_id');
            $table->text('access_token');
            $table->boolean('token_expired_flg')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('code_id')->references('id')->on('invite_codes');
            $table->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::create('ad_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('instagram_account_id');
            $table->string('facebook_ads_id');
            $table->string('facebook_ads_account_id');
            $table->string('business_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('last_crawled_ad_id')->default('');
            $table->rememberToken();
            $table->boolean('is_completed_tutorial')->default(false);
            $table->integer('max_search_condition')->default(10);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('instagram_account_id')->references('id')->on('instagram_accounts');
        });

        Schema::create('account_has_adaccount', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('ad_account_id');
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('ad_account_id')->references('id')->on('ad_accounts');
        });

        Schema::create('ad_account_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ad_account_id');
            $table->integer('permission');
            $table->timestamps();
            $table->foreign('ad_account_id')->references('id')->on('ad_accounts');
        });
    }
}
