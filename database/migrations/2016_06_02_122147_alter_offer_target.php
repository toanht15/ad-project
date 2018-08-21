<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOfferTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_target', function (Blueprint $t) {
            $t->dropColumn('image_url');
            $t->dropColumn('post_url');
            $t->dropColumn('author_url');
            $t->dropColumn('author_name');
            $t->dropColumn('author_icon_img');
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
