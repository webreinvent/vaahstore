<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInVhStOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_order_items', function($table) {
            $table->after('vh_st_vendor_id',function($table){
            $table->integer('quantity')->nullable()->index();
            $table->decimal('price', 10, 2)->nullable();
            });
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_order_items', function($table) {
            $table->dropColumn(['quantity','price']);
        });
    }
}
