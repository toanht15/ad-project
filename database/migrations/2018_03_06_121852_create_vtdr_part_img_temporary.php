<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVtdrPartImgTemporary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part_images_temporaries', function (Blueprint $t) {
            $t->increments('id');
            $t->bigInteger('post_id');
            $t->string('post_media_id', 100);
            $t->integer('search_condition_id');
            $t->integer('vtdr_image_id');
            $t->integer('vtdr_site_id');
            $t->integer('vtdr_part_id');
            $t->index('post_id');
            $t->index('post_media_id');
            $t->index('search_condition_id');
            $t->index('vtdr_site_id');
            $t->index('vtdr_part_id');
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
        Schema::drop('part_images_temporaries');
    }
}
