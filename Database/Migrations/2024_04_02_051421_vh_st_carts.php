<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStcarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_carts')) {
            Schema::create('vh_st_carts', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_user_id')->nullable()->index();
                $table->integer('vh_st_user_id')->nullable()->index();

                $table->string('name')->nullable()->index();
                $table->string('slug')->nullable()->index();
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
        Schema::dropIfExists('vh_st_carts');
    }
}
