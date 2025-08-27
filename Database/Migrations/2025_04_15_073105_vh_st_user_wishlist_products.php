<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStUserWishlistProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


            if (!Schema::hasTable('vh_st_user_wishlist_products')) {
                Schema::create('vh_st_user_wishlist_products', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->uuid('uuid')->nullable()->index();
                    $table->bigInteger('vh_st_user_wishlist_id')->nullable()->index();
                    $table->bigInteger('vh_st_product_id')->nullable()->index();
                    $table->bigInteger('vh_st_product_variation_id')->nullable()->index();
                    //----common fields
                    $table->text('meta')->nullable();
                    $table->bigInteger('created_by')->nullable()->index();
                    $table->bigInteger('updated_by')->nullable()->index();
                    $table->bigInteger('deleted_by')->nullable()->index();
                    $table->timestamps();
                    $table->softDeletes();
                    $table->index(['created_at', 'updated_at', 'deleted_at'], 'uwp_cud_idx');

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
        Schema::dropIfExists('vh_st_user_wishlist_products');
    }
}
