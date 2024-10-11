<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTaxonomyIdOrderStatusColumnInVhStOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_orders', function (Blueprint $table) {
            $table->renameColumn('taxonomy_id_order_status', 'order_status');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_orders', function (Blueprint $table) {
            $table->renameColumn('order_status', 'taxonomy_id_order_status');
        });
    }
}
