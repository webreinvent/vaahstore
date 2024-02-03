<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStBrands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable()->index();
            $table->integer('taxonomy_id_brand_status')->nullable()->index();
            $table->integer('registered_by')->nullable()->index();
            $table->integer('approved_by')->nullable();

            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();

            $table->dateTime('registered_at')->nullable();

            $table->dateTime('approved_at')->nullable();

            $table->boolean('is_active')->nullable()->index();
            $table->boolean('is_default')->default(0)->nullable()->index();
            $table->string('status_notes')->nullable();

            //----common fields
            $table->text('meta')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable()->index();
            $table->integer('deleted_by')->nullable()->index();
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
        Schema::dropIfExists('vh_st_brands');
    }
}
