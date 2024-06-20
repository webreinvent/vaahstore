<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStOrderPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_order_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable()->index();
            $table->bigInteger('vh_st_payment_id')->nullable()->index();
            $table->bigInteger('vh_st_order_id')->nullable()->index();
            $table->integer('payable_amount')->nullable()->index();
            $table->double('payment_amount_paid')->nullable()->index();
            $table->integer('remaining_payable_amount')->nullable()->index();
            $table->dateTime('date')->nullable()->index();
            //----common fields
            $table->text('meta')->nullable();
            $table->bigInteger('created_by')->nullable()->index();
            $table->bigInteger('updated_by')->nullable()->index();
            $table->bigInteger('deleted_by')->nullable()->index();
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
        Schema::dropIfExists('vh_st_order_payments');
    }
}
