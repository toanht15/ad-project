<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwJobResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_stat_job_results', function(Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('job_id');
            $t->longText('result');
            $t->timestamps();
            $t->foreign('job_id')->references('id')->on('twitter_stat_jobs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('twitter_stat_job_results');
    }
}
