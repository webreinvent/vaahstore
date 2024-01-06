<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsAvailableAtAndLaunchAtInVhStProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->dateTime('available_at')->nullable()->after('in_stock');
            $table->dateTime('launch_at')->nullable()->after('available_at');
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
            $table->dropColumn('available_at');
            $table->dropColumn('launch_at');
        });
    }
}
