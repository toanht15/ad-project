<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterImageTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function (Blueprint $t) {
            $t->unsignedInteger('origin_author_id')->after('id');
            $t->unsignedInteger('author_id')->after('id')->nullable();
            $t->foreign('author_id')->references('id')->on('authors');
            $t->foreign('origin_author_id')->references('id')->on('authors');
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
