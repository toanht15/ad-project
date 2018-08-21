<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archived_posts', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('ad_account_id');
            $t->unsignedInteger('created_account_id');
            $t->unsignedBigInteger('post_id');
            $t->timestamps();
            $t->foreign('ad_account_id')->references('id')->on('ad_accounts');
            $t->foreign('post_id')->references('id')->on('posts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('archived_posts');
    }
}
