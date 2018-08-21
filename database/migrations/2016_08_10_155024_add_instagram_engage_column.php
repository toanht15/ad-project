<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstagramEngageColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authors', function (Blueprint $t) {
            $t->integer('follower')->after('icon_img');
            $t->integer('following')->after('icon_img');
            $t->integer('post_count')->after('icon_img');
        });

        Schema::table('post', function (Blueprint $t) {
            $t->integer('like')->after('pub_date');
            $t->integer('comment')->after('pub_date');
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
