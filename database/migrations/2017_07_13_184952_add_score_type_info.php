<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScoreTypeInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('score_types', function(Blueprint $t) {
            $t->float('standard')->after('reliability_score');
            $t->text('features')->after('reliability_score');
            $t->string('lib_path')->after('reliability_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('score_types', function(Blueprint $t) {
            $t->dropColumn('features');
            $t->dropColumn('lib_path');
            $t->dropColumn('standard');
        });
    }
}
