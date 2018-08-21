<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameKeysInOfferSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // rename keys to regular name
        Schema::table('offer_sets', function ($table) {
            $table->index('ad_account_id', 'offer_sets_ad_account_id_foreign');
            $table->dropIndex('offer_ad_account_id_foreign');
            $table->index('create_account_id', 'offer_sets_create_account_id_foreign');
            $table->dropIndex('offer_create_account_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_sets', function ($table) {
            $table->index('ad_account_id', 'offer_ad_account_id_foreign');
            $table->index('create_account_id', 'offer_create_account_id_foreign');
        });
    }
}
