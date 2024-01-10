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
            $table->string('seo_title')->nullable()->index()->after('meta');
            $table->text('seo_meta_description')->nullable()->after('seo_title');
            $table->string('seo_meta_keyword')->nullable()->index()->after('seo_meta_description');
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
            $table->dropColumn('available_at');
            $table->dropColumn('launch_at');
        });
    }
}
