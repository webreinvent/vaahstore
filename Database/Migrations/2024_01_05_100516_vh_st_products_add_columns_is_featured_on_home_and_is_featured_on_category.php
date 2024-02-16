<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProductsAddColumnsIsFeaturedOnHomeAndIsFeaturedOnCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->after('in_stock', function ($table) {
                $table->boolean('is_featured_on_home_page')->nullable()->index();
                $table->boolean('is_featured_on_category_page')->nullable()->index();
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
        Schema::table('vh_st_products', function (Blueprint $table) {
            $table->dropColumn('is_featured_on_home_page');
            $table->dropColumn('is_featured_on_category_page');
        });
    }
}
