<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStUserWishlists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        if (!Schema::hasTable('vh_st_user_wishlists')) {
            Schema::create('vh_st_user_wishlists', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('uuid')->nullable()->index();
                $table->bigInteger('vh_st_wishlist_id')->nullable()->index();
                $table->bigInteger('vh_user_id')->nullable()->index();
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
        Schema::dropIfExists('vh_st_user_wishlists');
    }
}
