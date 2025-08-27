<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVhStStoreIdColumnInVhStProductVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vh_st_product_vendors', function (Blueprint $table) {
            $table->integer('vh_st_store_id')->nullable()->index()->after('uuid');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_product_vendors', function (Blueprint $table) {
            $table->dropColumn('vh_st_store_id');
        });
    }
}
