<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStaddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('vh_st_addresses')) {
            Schema::create('vh_st_addresses', function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->uuid('uuid')->nullable()->index();
                $table->integer('vh_user_id')->nullable()->index();
                $table->integer('taxonomy_id_address_types')->nullable()->index();
                $table->integer('taxonomy_id_address_status')->nullable()->index();

                $table->string('address_line_1')->nullable()->index();
                $table->string('address_line_2')->nullable()->index();
                $table->string('name')->nullable()->index();
                $table->bigInteger('phone')->nullable()->index();
                $table->string('pin_code')->nullable()->index();
                $table->string('city')->nullable()->index();
                $table->string('state')->nullable()->index();
                $table->string('country')->nullable()->index();

                //----common fields
                $table->text('meta')->nullable();
                $table->text('status_notes')->nullable();
                $table->bigInteger('is_default')->nullable()->index();
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

    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_st_addresses');
    }
}
