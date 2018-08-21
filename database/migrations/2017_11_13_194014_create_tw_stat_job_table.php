<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwStatJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_stat_jobs', function(Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('media_account_id');
            $t->string('job_id');
            $t->dateTime('start_time');
            $t->dateTime('end_time');
            $t->string('segmentation_type');
            $t->string('url');
            $t->string('entity_ids');
            $t->string('placement');
            $t->dateTime('expires_at')->nullable();
            $t->string('status');
            $t->string('granularity');
            $t->string('entity');
            $t->string('metric_groups');
            $t->timestamps();

            $t->foreign('media_account_id')->references('id')->on('media_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('twitter_stat_jobs');
    }
}
