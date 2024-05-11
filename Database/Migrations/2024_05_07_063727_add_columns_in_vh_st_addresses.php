<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInVhStAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_addresses', function($table) {
            $table->after('address_line_2',function($table){
                $table->string('name')->nullable()->index();
                $table->bigInteger('phone')->nullable()->index();
                $table->string('pin_code')->nullable()->index();
                $table->string('city')->nullable()->index();
                $table->string('state')->nullable()->index();
                $table->string('country')->nullable()->index();
            });
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_addresses', function($table) {
            $table->dropColumn(['name','phone','pin_code','city','state','country']);

        });
    }
}
