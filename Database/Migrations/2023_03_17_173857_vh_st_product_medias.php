<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStproductmedias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_product_medias')) {
            Schema::create('vh_st_product_medias', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_st_product_id')->nullable()->index();
                $table->integer('vh_st_product_variation_id')->nullable()->index();
                $table->integer('taxonomy_id_product_media_status')->nullable()->index();

                $table->string('status_notes')->nullable();
                $table->string('url_image')->nullable();
                $table->bigInteger('image_size')->nullable()->index();
                $table->string('thumbnail_url')->nullable();
                $table->bigInteger('thumbnail_size')->nullable()->index();
                $table->string('mime_type')->nullable();
                $table->string('url')->nullable();
                $table->string('extension')->nullable();
                $table->string('original_name')->nullable();
                $table->string('type')->nullable();
                $table->string('uploaded_file_name')->nullable();
                $table->string('image_slug')->nullable();
                $table->string('path')->nullable();
                $table->string('full_path')->nullable();
                $table->string('full_url_image')->nullable();
                $table->string('image_name')->nullable();
                $table->string('thumbnail_name')->nullable();
                $table->boolean('is_active')->nullable()->index();


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
        Schema::dropIfExists('vh_st_product_medias');
    }
}
