<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLowStockAtInVhStProductVariation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_variations', function($table) {
            $table->dateTime('low_stock_at')->nullable()->after('deleted_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vh_st_product_variations', function($table) {
            $table->dropColumn('low_stock_at');
        });
    }
}
