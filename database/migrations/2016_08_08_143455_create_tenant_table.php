<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->unsignedInteger('created_admin_id');
            $t->dateTime('contract_start_date');
            $t->dateTime('contract_end_date');
            $t->foreign('created_admin_id')->references('id')->on('admins');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::table('account', function (Blueprint $t) {
            $t->unsignedInteger('tenant_id')->nullable()->after('id');
            $t->foreign('tenant_id')->references('id')->on('tenants');
        });

        Schema::table('invite_codes', function (Blueprint $t) {
            $t->unsignedInteger('tenant_id')->nullable()->after('id');
            $t->foreign('tenant_id')->references('id')->on('tenants');
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
