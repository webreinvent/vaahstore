<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProductsChangeSeoMetaKeywordColumnDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

        public function up()
    {

        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->json('seo_meta_keyword')->change();
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
            $table->string('seo_meta_keyword')->change();
        });
    }
}
