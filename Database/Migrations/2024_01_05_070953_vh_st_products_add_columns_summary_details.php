<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProductsAddColumnsSummaryDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->string('summary')->nullable()->after('slug');
            $table->text('details')->nullable()->after('summary');
        });
    }


    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->dropColumn('summary');
            $table->dropColumn('details');
        });
    }
}
