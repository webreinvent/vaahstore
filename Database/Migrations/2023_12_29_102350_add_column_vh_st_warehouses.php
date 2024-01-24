<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVhStWarehouses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_warehouses', function($table) {
            $table->string('postal_code')->nullable()->index()->after('is_active');
            $table->string('address_1')->nullable()->after('postal_code');
            $table->string('address_2')->nullable()->after('address_1');

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_warehouses', function($table) {
            $table->dropColumn(['address_1','address_2','postal_code']);

        });

    }
}
