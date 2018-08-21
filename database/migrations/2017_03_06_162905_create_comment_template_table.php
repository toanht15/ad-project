<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_templates', function (Blueprint $t) {
            $t->increments('id');
            $t->string('prefix');
            $t->string('suffix');
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::table('offers', function (Blueprint $t) {
            $t->unsignedInteger('comment_temp_id')->nullable()->after('ad_account_id');
            $t->foreign('comment_temp_id')->references('id')->on('comment_templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $t) {
            $t->dropColumn('comment_temp_id');
        });

        Schema::drop('comment_templates');
    }
}
