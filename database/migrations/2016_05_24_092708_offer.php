<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Offer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //オファーリスト下書き
        Schema::create('offer_draft', function (Blueprint $table) {
            $table->string('post_id');
            $table->integer('ad_account_id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer_draft CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer_draft COMMENT 'オファー予定のpost_id格納'");

        //オファー
        Schema::create('offer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pm_id'); //パーミッションマネージャから戻る値
            $table->integer('target_count');
            $table->string('title');
            $table->text('comment');
            $table->string('answer_tag');
            $table->integer('ad_account_id');
            $table->integer('create_account_id');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer COMMENT 'オファー'");

        //オファー 先
        Schema::create('offer_target', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id');
            $table->string('post_id');
            $table->integer('ad_account_id');
            $table->integer('create_account_id');
            $table->text('image_url');
            $table->text('post_url');
            $table->text('author_url');
            $table->text('author_name');
            $table->text('author_icon_img');
            $table->integer('status'); //0:offer_targetへ保存ずみ  1:PMヘオファー済み 2:OK 3:NG
            $table->timestamps();
        });
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer_target CHARACTER SET utf8mb4");
        DB::statement("ALTER TABLE ".DB::getTablePrefix()."offer_target COMMENT 'オファーのターゲットとなるpost_idとその情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('offer');
        Schema::drop('offer_draft');
        Schema::drop('offer_target');
    }
}
