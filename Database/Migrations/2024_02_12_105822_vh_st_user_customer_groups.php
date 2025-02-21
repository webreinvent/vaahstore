<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStUserCustomerGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_user_customer_groups')) {
            Schema::create('vh_st_user_customer_groups', function (Blueprint $table) {
                $table->increments('id');

                $table->uuid('uuid')->nullable()->index();
                $table->bigInteger('vh_st_user_id')->index();

                $table->bigInteger('vh_st_customer_group_id')->index();

                //----common fields
                $table->text('meta')->nullable();
                $table->integer('created_by')->nullable()->index();
                $table->integer('updated_by')->nullable()->index();
                $table->integer('deleted_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['created_at', 'updated_at', 'deleted_at'], 'vh_st_user_customer_groups');
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
        Schema::dropIfExists('vh_st_user_customer_groups');
    }
}
