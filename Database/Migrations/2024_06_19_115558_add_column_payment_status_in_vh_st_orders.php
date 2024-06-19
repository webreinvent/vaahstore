<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPaymentStatusInVhStOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_orders', function($table) {
            $table->after('taxonomy_id_order_status',function($table){
                $table->integer('taxonomy_id_payment_status')->nullable()->index();
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
            $table->dropColumn(['taxonomy_id_payment_status']);
        });
    }
}
