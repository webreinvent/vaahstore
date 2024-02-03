<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStproductstocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_product_stocks')) {
            Schema::create('vh_st_product_stocks', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();

                $table->integer('vh_st_vendor_id')->nullable()->index();
                $table->integer('vh_st_product_id')->nullable()->index();
                $table->integer('vh_st_product_variation_id')->nullable()->index();
                $table->integer('vh_st_warehouse_id')->nullable()->index();
                $table->integer('taxonomy_id_product_stock_status')->nullable()->index();
                $table->string('name')->nullable()->index();
                $table->string('slug')->nullable()->index();
                $table->integer('quantity')->nullable()->index();
                $table->boolean('is_active')->nullable()->index();
                $table->string('status_notes')->nullable();


                //----common fields
                $table->text('meta')->nullable();
                $table->bigInteger('created_by')->nullable()->index();
                $table->bigInteger('updated_by')->nullable()->index();
                $table->bigInteger('deleted_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['created_at']);
                $table->index(['updated_at']);
                $table->index(['deleted_at']);
                //----/common fields

            });
        }

    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_st_product_stocks');
    }
}
