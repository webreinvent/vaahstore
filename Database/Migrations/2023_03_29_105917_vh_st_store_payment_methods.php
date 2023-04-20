<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStstorepaymentmethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_store_payment_methods')) {
            Schema::create('vh_st_store_payment_methods', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_st_store_id')->nullable()->index();
                $table->integer('vh_st_payment_method_id')->nullable()->index();
                $table->integer('taxonomy_id_payment_status')->nullable()->index();

                $table->dateTime('last_payment_at')->nullable();

                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->boolean('is_active')->nullable()->index();


                //----common fields
                $table->string('status_notes')->nullable();
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
        Schema::dropIfExists('vh_st_store_payment_methods');
    }
}
