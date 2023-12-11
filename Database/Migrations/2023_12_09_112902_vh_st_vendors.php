<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStVendorsAddDetailsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_vendors', function (Blueprint $table) {
            $table->bigInteger('phone_number')->nullable()->after('slug');
            $table->string('email')->nullable()->after('phone_number');
            $table->text('address')->nullable()->after('email');
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
            $table->dropColumn('phone_number');
            $table->dropColumn('email');
            $table->dropColumn('address');
        });
    }
}
