<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVhStProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_variations', function($table) {
            $table->longText('description')->nullable()->index();
            $table->integer('per_unit_price')->nullable()->index();
            $table->integer('total_price')->nullable()->index();
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
            $table->dropColumn(['description','per_unit_price','total_price']);
        });
    }
}
