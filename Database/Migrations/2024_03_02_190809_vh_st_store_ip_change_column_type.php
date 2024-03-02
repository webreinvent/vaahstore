<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStStoreIpChangeColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {

        Schema::table('vh_st_stores', function (Blueprint $table) {
            $table->string('allowed_ips')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vh_st_stores', function (Blueprint $table) {
            $table->json('allowed_ips')->change();
        });
    }
}
