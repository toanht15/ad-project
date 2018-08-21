<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function (Blueprint $t) {
            $t->increments('id');
            $t->string('media_id');
            $t->string('profile_url', 1024);
            $t->string('name');
            $t->string('icon_img', 1024);
            $t->timestamps();
        });

        Schema::table('post', function (Blueprint $t) {
            $t->dropColumn('author_url');
            $t->dropColumn('author_name');
            $t->dropColumn('author_icon_img');
            $t->unsignedInteger('author_id')->after('post_url');
            $t->foreign('author_id')->references('id')->on('authors');
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
