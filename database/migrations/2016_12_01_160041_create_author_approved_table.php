<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorApprovedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author_approveds', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedInteger('ad_account_id');
            $t->unsignedInteger('author_id');
            $t->integer('approved');
            $t->timestamps();
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
