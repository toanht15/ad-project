<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Announsement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //お知らせ
        Schema::create('announsement', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title');
            $table->text('body');
            $table->boolean('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."announsement CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."announsement COMMENT 'お知らせ用テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('announsement');
    }
}
