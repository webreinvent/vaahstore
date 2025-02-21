<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsPreferredInVhStProductVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_product_vendors', function($table) {
            $table->boolean('is_preferred')->nullable()->index()->after('is_active');

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_product_vendors', function($table) {
            $table->dropColumn(['is_preferred']);
        });    }
}
