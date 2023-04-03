<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStorderitems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_order_items')) {
            Schema::create('vh_st_order_items', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_order_id')->nullable()->index();
                $table->integer('vh_user_id')->nullable()->index();
                $table->integer('vh_st_customer_group_id')->nullable()->index();
                $table->integer('taxonomy_id_order_type_status')->nullable()->index();
                $table->integer('taxonomy_id_order_items_status')->nullable()->index();
                $table->integer('vh_shiping_address_id')->nullable()->index();
                $table->integer('vh_billing_address_id')->nullable()->index();
                $table->integer('vh_st_product_id')->nullable()->index();
                $table->integer('vh_st_product_variation_id')->nullable()->index();
                $table->integer('vh_st_vendor_id')->nullable()->index();

                $table->string('is_invoice_available')->nullable()->index();

                $table->string('invoice_url')->nullable()->index();
                $table->string('tracking')->nullable()->index();
                $table->boolean('is_active')->nullable()->index();


                //----common fields
                $table->text('meta')->nullable();
                $table->text('status_notes')->nullable();
                $table->bigInteger('created_by')->nullable()->index();
                $table->bigInteger('updated_by')->nullable()->index();
                $table->bigInteger('deleted_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['created_at', 'updated_at', 'deleted_at']);
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
        Schema::dropIfExists('vh_st_order_items');
    }
}
