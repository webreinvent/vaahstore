<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStpayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_payments')) {
            Schema::create('vh_st_payments', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->bigInteger('vh_st_payment_method_id')->nullable()->index();
                $table->string('transaction_id')->nullable()->index();
                $table->bigInteger('taxonomy_id_payment_status')->nullable()->index();
                $table->integer('amount')->nullable()->index();
                $table->string('status_notes')->nullable();
                $table->string('notes')->nullable();
                $table->boolean('is_active')->nullable()->index();
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

    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_st_payments');
    }
}
