<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVhUserIdInVhStCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_carts', function($table) {
            $table->integer('vh_user_id')->nullable()->index()->after('uuid');

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_carts', function($table) {
            $table->dropColumn(['vh_user_id']);
        });
    }
}
