<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Images extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_target_id');
            $table->integer('ad_account_id');
            $table->text('origin_image_url');
            $table->text('content_type');
            $table->text('image_path');
            $table->text('image_name');
            $table->text('option');
            $table->boolean('is_origin')->default(0);
            $table->boolean('uploaded')->default(0);
            $table->text('uploaded_url');
            $table->integer('create_account_id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."images CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."images COMMENT '画像'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
    }
}
