<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLowStockAndQuantityLowColumnInProductVariationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_variations', function($table) {
            $table->after('is_mail_sent', function (Blueprint $table) {
                $table->dateTime('low_stock_at')->nullable()->index();
                $table->boolean('is_quantity_low')->nullable()->index();
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
        Schema::table('vh_st_product_variations', function($table) {
            $table->dropColumn(['low_stock_at', 'is_quantity_low']);
        });
    }
}
