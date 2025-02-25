<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeoColumnsInVhStProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->after('meta', function ($table) {
                $table->string('seo_title')->nullable()->index();
                $table->string('seo_meta_description')->nullable();
                $table->string('seo_meta_keyword')->nullable();
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
        Schema::table('vh_st_products', function($table) {
            $table->dropColumn(['seo_title','seo_meta_description','seo_meta_keyword']);
        });
    }
}
