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
            $table->after('vh_st_payment_method_id',function($table){
                $table->integer('taxonomy_id_payment_status')->nullable()->index();
                $table->string('order_shipment_status')->nullable()->index();
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
        Schema::table('vh_st_orders', function($table) {
            $table->dropColumn(['taxonomy_id_payment_status','order_shipment_status']);
        });
    }
}
