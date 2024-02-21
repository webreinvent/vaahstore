<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStVendorsAddColumnsPhoneEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_vendors', function($table) {

                     $table->after('slug', function ($table) {
                    $table->bigInteger('phone_number')->nullable();
                    $table->string('email')->nullable()->index();
                    $table->text('address')->nullable();

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
        Schema::table('vh_st_vendors', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('email');
            $table->dropColumn('address');
        });
    }
}
