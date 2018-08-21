<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\Schema;

class Account extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //account
        Schema::create('account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('profile_image') ;
            $table->string('facebook_id')->unique();
            $table->text('access_token');
            $table->string('ad_account_id');
            $table->integer('status')->default(1);
            $table->string('email');
            $table->text('password') ;
            $table->text('remember_token');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."account CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."account COMMENT 'login facebook account)'");

        //広告ID
        Schema::create('ad_account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('instagram_account_id')->unique()->nullable();
            $table->string('facebook_ads_id')->unique();
            $table->string('facebook_ads_account_id');
            $table->string('business_id')->nullable();
            $table->string('business_name')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."ad_account CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."ad_account COMMENT 'facebook ad account'");

        //Instagram ID
        Schema::create('instagram_account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('instagram_id');
            $table->text('profile_image');
            $table->integer('ad_account_id')->unique()->nullable();
            $table->text('access_token');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."instagram_account CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."instagram_account COMMENT 'instagram account'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('account');
        Schema::drop('ad_account');
        Schema::drop('instagram_account');
    }
}
