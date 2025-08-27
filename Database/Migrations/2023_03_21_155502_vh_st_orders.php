<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStorders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_orders')) {
            Schema::create('vh_st_orders', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_user_id')->nullable()->index();
                $table->integer('vh_st_store_id')->nullable()->index();
                $table->string('order_status')->nullable()->index();
                $table->integer('taxonomy_id_payment_status')->nullable()->index();
                $table->integer('vh_st_payment_method_id')->nullable()->index();
                $table->string('order_shipment_status')->nullable()->index();

                $table->decimal('amount', 10, 2)->nullable()->index();
                $table->decimal('delivery_fee', 10, 2)->nullable()->index();
                $table->decimal('taxes', 10, 2)->nullable()->index();
                $table->decimal('discount', 10, 2)->nullable()->index();
                $table->decimal('payable', 10, 2)->nullable()->index();
                $table->decimal('paid', 10, 2)->nullable()->index();

                $table->decimal('amount_in_paid_currency', 10, 2)->nullable();
                $table->string('paid_currency_code')->nullable();
                $table->decimal('exchange_rate', 10, 6)->nullable();
                $table->boolean('is_paid')->nullable()->index();
                $table->boolean('is_active')->nullable()->index();

                //----common fields
                $table->text('meta')->nullable();
                $table->text('status_notes')->nullable();
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
        Schema::dropIfExists('vh_st_orders');
    }
}
