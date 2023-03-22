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
                $table->integer('taxonomy_id_order_status')->nullable()->index();
                $table->integer('taxonomy_id_payment_method')->nullable()->index();

                $table->integer('amount')->nullable()->index();
                $table->double('delivery_fee')->nullable()->index();
                $table->double('taxes')->nullable()->index();
                $table->double('discount')->nullable()->index();
                $table->double('payable')->nullable()->index();
                $table->double('paid')->nullable()->index();
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
        Schema::dropIfExists('vh_st_orders');
    }
}
