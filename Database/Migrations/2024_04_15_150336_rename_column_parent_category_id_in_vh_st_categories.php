<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnParentCategoryIdInVhStCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_categories', function($table) {
            $table->renameColumn('parent_category_id', 'category_id');

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_categories', function (Blueprint $table) {
            $table->renameColumn('category_id', 'parent_category_id');
        });
    }
}
