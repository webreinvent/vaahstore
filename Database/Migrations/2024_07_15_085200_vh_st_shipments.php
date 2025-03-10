<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStshipments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_shipments')) {
            Schema::create('vh_st_shipments', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->bigInteger('taxonomy_id_shipment_status')->nullable()->index();
                $table->string('name')->nullable()->index();
                $table->string('tracking_url')->nullable()->index();
                $table->string('tracking_key')->nullable()->index();
                $table->string('tracking_value')->nullable()->index();
                $table->integer('total_shipment')->nullable()->index();
                $table->boolean('is_trackable')->nullable()->index();


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
        Schema::dropIfExists('vh_st_shipments');
    }
}
