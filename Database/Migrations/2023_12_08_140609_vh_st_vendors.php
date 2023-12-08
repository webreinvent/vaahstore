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
            $table->integer('years_in_business')->nullable()->after('slug');
            $table->text('services_offered')->nullable()->after('years_in_business');
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
            $table->dropColumn('years_in_business');
            $table->dropColumn('services_offered');
        });
    }
}
