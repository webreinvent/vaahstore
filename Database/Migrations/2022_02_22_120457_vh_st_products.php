<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_products', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable()->index();

            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();

            $table->integer('taxonomy_id_product_type')->nullable()->index();

            $table->integer('vh_st_store_id')->nullable()->index();
            $table->integer('vh_st_brand_id')->nullable()->index();
            $table->integer('vh_cms_content_form_field_id')->nullable()->index();

            $table->bigInteger('quantity')->nullable()->index();
            $table->boolean('in_stock')->nullable()->index();

            $table->boolean('is_active')->nullable()->index();
            $table->string('status')->nullable()->index();
            $table->string('status_notes')->nullable();

            //----common fields
            $table->json('meta')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable()->index();
            $table->integer('deleted_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['created_at', 'updated_at', 'deleted_at']);
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
        Schema::dropIfExists('vh_st_products');
    }
}
