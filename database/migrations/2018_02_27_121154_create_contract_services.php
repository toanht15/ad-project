<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('advertiser_id');
            $table->tinyInteger('service_type');
            $table->integer('vtdr_site_id')->nullable();
            $table->integer('max_media_account')->nullable();
            $table->foreign('advertiser_id')->references('id')->on('advertisers');
            $table->timestamps();
        });

        Schema::create('contract_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contract_service_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreign('contract_service_id')->references('id')->on('contract_services');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contract_services');
        Schema::drop('contract_schedules');
    }
}
