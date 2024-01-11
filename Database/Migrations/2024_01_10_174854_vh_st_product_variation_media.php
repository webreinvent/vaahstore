<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProductVariationMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_prod_variation_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vh_st_product_media_id')->nullable()->index();
            $table->integer('vh_st_product_id')->nullable()->index();
            $table->integer('vh_st_product_variation_id')->nullable()->index();
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

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_st_prod_variation_media');
    }
}
