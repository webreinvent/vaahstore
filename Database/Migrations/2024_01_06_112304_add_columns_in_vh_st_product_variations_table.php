<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInVhStProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_variations', function($table) {
            $table->longText('description')->nullable()->after('is_active');
            $table->integer('per_unit_price')->nullable()->index()->after('description');
            $table->integer('total_price')->nullable()->index()->after('per_unit_price');
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
            $table->dropColumn(['per_unit_price','total_price','description']);
        });
    }
}
