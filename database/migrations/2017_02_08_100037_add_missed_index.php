<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissedIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hashtag_has_post', function (Blueprint $t) {
            $t->index(['hashtag_id', 'post_id']);
        });

        Schema::table('posts', function (Blueprint $t) {
            $t->index('post_id');
        });

        Schema::table('authors', function (Blueprint $t) {
            $t->index('media_id');
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
