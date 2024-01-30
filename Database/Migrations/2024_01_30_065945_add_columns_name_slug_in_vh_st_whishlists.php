<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsNameSlugInVhStWhishlists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_whishlists', function (Blueprint $table) {
            $table->string('name')->nullable()->index()->after('uuid');
            $table->string('slug')->nullable()->index()->after('name');
            $table->boolean('type')->nullable()->index()->after('slug');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('vh_st_whishlists', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('slug');
            $table->dropColumn('type');
        });
    }
}
