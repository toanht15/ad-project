<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function (Blueprint $t) {
            $t->dropColumn('origin_image_url');
            $t->dropColumn('content_type');
            $t->dropColumn('image_name');
            $t->dropColumn('option');
            $t->dropColumn('uploaded');
            $t->dropColumn('uploaded_url');
            $t->dropColumn('is_origin');
            $t->renameColumn('image_path', 'image_url');
            $t->unsignedInteger('offer_target_id')->change();
            $t->unsignedInteger('ad_account_id')->change();
            $t->unsignedInteger('create_account_id')->change();
            $t->foreign('offer_target_id')->references('id')->on('offer_target');
            $t->foreign('ad_account_id')->references('id')->on('ad_account');
            $t->foreign('create_account_id')->references('id')->on('account');
        });

        Schema::table('facebook_image_entries', function (Blueprint $t) {
            $t->dropForeign('facebook_image_entries_offer_target_id_foreign');
            $t->renameColumn('offer_target_id', 'image_id');
            $t->foreign('image_id')->references('id')->on('images');
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
