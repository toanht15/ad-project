<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCascadeDeleteSlideshowImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('slideshow_images', function (Blueprint $table) {
            $table->dropForeign('slideshow_images_image_id_foreign');
            $table->foreign('image_id')
                ->references('id')->on('images')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('slideshow_images', function (Blueprint $table) {
            $table->dropForeign('slideshow_images_image_id_foreign');
            $table->foreign('image_id')->references('id')->on('images');
        });
    }
}
