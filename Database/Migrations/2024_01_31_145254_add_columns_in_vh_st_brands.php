<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInVhStBrands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_brands', function($table) {
            $table->string('image')->nullable()->index()->after('slug');
            $table->string('meta_title')->nullable()->index()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->index()->after('meta_description');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vh_st_brands', function($table) {
            $table->dropColumn(['image','meta_title','meta_description','meta_keywords']);
        });
    }
}
