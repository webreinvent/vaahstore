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

        Schema::create('vh_st_vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable()->index();
            $table->integer('vh_st_store_id')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();
            $table->string('country_code')->nullable();
            $table->bigInteger('phone_number')->nullable();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->integer('taxonomy_id_vendor_business_type')->nullable();
            $table->string('business_document_type')->nullable();
            $table->string('business_document_detail')->nullable();
            $table->string('business_document_file')->nullable();
            $table->integer('years_in_business')->nullable()->index();
            $table->text('services_offered')->nullable();
            $table->integer('owned_by')->nullable()->index();
            $table->dateTime('registered_at')->nullable();
            $table->boolean('auto_approve_products')->nullable();

            $table->integer('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->boolean('is_default')->nullable()->index();
            $table->boolean('is_active')->nullable()->index();
            $table->integer('taxonomy_id_vendor_status')->nullable()->index();
            $table->string('status_notes')->nullable();

            //----common fields
            $table->text('meta')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable()->index();
            $table->integer('deleted_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['created_at']);
            $table->index(['updated_at']);
            $table->index(['deleted_at']);
            //----/common fields

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vh_st_vendors');
    }
}
