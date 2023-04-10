<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VhStMediaImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { if (!Schema::hasTable('vh_st_media_images')) {
        Schema::create('vh_st_media_images', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->nullable()->index();
            $table->integer('vh_st_product_id')->nullable()->index();
            $table->string('image_path')->nullable()->index();

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
        Schema::dropIfExists('vh_st_media_images');
    }
}
