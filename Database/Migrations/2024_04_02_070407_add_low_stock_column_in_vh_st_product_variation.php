<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLowStockColumnInVhStProductVariation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_variations', function($table) {
            $table->boolean('is_quantity_low')->nullable()->after('is_mail_sent');
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
            $table->dropColumn('is_quantity_low');
        });
    }
}
