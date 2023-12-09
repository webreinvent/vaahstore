<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('vh_st_vendors', function (Blueprint $table) {
            $table->string('business_document_type')->nullable()->after('	taxonomy_id_vendor_business_type');
            $table->string('business_document_detail')->nullable()->after('business_document_type');
            $table->string('business_document_file')->nullable()->after('business_document_detail');
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
            $table->dropColumn('business_document_type');
            $table->dropColumn('business_document_detail');
            $table->dropColumn('business_document_file');
        });
    }
}
