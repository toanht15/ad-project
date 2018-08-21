<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactoringDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('account', 'accounts');
        Schema::rename('ad_account', 'ad_accounts');
        Schema::rename('hashtag', 'hashtags');
        Schema::rename('instagram_account', 'instagram_accounts');
        Schema::rename('offer', 'offer_sets');
        Schema::table('offer_target', function (Blueprint $t) {
            $t->renameColumn('offer_id', 'offer_set_id');
        });
        Schema::rename('offer_target', 'offers');
        Schema::rename('post', 'posts');
        Schema::drop('offer_draft');
        Schema::table('images', function (Blueprint $t) {
            $t->renameColumn('offer_target_id', 'offer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('accounts', 'account');
        Schema::rename('ad_accounts', 'ad_account');
        Schema::rename('hashtags', 'hashtag');
        Schema::rename('instagram_accounts', 'instagram_account');
        Schema::rename('offer_sets', 'offer');
        Schema::rename('offers', 'offer_target');
        Schema::rename('posts', 'post');
    }
}
