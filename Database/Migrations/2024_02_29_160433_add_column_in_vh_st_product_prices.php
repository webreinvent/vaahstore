<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInVhStProductPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_prices', function($table) {
            $table->integer('vh_st_vendor_product_id')->nullable()->index()->after('id');

        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vh_st_product_prices', function($table) {
            $table->dropColumn(['vh_st_vendor_product_id']);
        });
    }
}
