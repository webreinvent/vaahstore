<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdColumnInVhStCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_categories', function($table) {
            $table->integer('parent_category_id')->nullable()->index()->after('uuid');

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_categories', function($table) {
            $table->dropColumn(['parent_category_id']);
        });    }
}
