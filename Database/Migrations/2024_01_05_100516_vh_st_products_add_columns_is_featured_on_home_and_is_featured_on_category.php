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
            $table->boolean('is_featured_on_home_page')->nullable()->index()->after('in_stock');
            $table->boolean('is_featured_on_category_page')->nullable()->index()->after('is_featured_on_home_page');
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
