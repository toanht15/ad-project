<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminAndUserPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_account_permissions', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('ad_account_id');
            $t->integer('permission');
            $t->timestamps();
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
        });

        Schema::create('admin_codes', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code');
            $t->boolean('is_used');
            $t->dateTime('expire_date');
            $t->timestamps();
        });

        Schema::create('admins', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('code_id');
            $t->string('name');
            $t->string('facebook_id');
            $t->text('access_token');
            $t->string('profile_image');
            $t->rememberToken();
            $t->timestamps();
            $t->foreign('code_id')->references('id')->on('admin_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('admins');
        Schema::drop('admin_codes');
        Schema::drop('ad_account_permissions');
    }
}
