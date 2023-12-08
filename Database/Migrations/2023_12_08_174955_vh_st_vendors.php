<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_vendors', function (Blueprint $table) {
            $table->integer('taxonomy_id_vendor_business_type')->nullable()->after('slug');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_vendors', function (Blueprint $table) {
            $table->dropColumn('taxonomy_id_vendor_business_type');
        });
    }
}
