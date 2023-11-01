<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStProductMediasImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vh_st_product_medias_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vh_st_product_media_id')->nullable()->index();

            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('url')->nullable();
            $table->string('path')->nullable();
            $table->bigInteger('size')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('url_thumbnail')->nullable();
            $table->bigInteger('thumbnail_size')->nullable()->index();
            $table->string('status_notes')->nullable();
            $table->boolean('is_active')->nullable()->index();

            //----common fields
            $table->text('meta')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable()->index();
            $table->integer('deleted_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // Change the index name to something shorter
            $table->index(['created_at', 'updated_at', 'deleted_at'], 'common_fields_index');
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
        Schema::dropIfExists('vh_st_product_medias_images');
    }
}
