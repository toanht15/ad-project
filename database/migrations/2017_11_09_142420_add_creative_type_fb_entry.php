<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreativeTypeFbEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facebook_image_entries', function(Blueprint $t) {
            $t->string('creative_type')->after('hash_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facebook_image_entries', function(Blueprint $t) {
            $t->dropColumn('creative_type');
        });
    }
}
