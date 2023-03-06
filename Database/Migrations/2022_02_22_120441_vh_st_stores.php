<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_stores', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable()->index();
            $table->string('name')->nullable()->index();
            $table->string('slug')->nullable()->index();

            $table->mediumText('notes')->nullable();
            $table->boolean('is_multi_currency')->nullable()->index();
            $table->boolean('is_multi_lingual')->nullable()->index();
            $table->boolean('is_multi_vendor')->nullable()->index();
            $table->json('allowed_ips')->nullable();

            $table->boolean('is_default')->nullable()->index();
            $table->boolean('is_active')->nullable()->index();
            $table->integer('taxonomy_id_store_status')->nullable()->index();
            $table->string('status_notes')->nullable();

            //----common fields
            $table->text('meta')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->integer('updated_by')->nullable()->index();
            $table->integer('deleted_by')->nullable()->index();
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
        Schema::dropIfExists('vh_st_stores');
    }
}
