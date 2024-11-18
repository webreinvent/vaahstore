<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VhStSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('vh_st_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->nullable()->index();
            $table->string('label')->nullable();
            $table->string('excerpt')->nullable();
            $table->string('type')->nullable()->index();
            $table->string('key')->nullable()->index();
            $table->text('value')->nullable();

            $table->bigInteger('vh_user_id')
                ->unsigned()->nullable()->index();
            $table->json('meta')->nullable();

            $table->bigInteger('created_by')
                ->unsigned()->nullable()->index();


            $table->bigInteger('updated_by')
                ->unsigned()->nullable()->index();


            $table->bigInteger('deleted_by')
                ->unsigned()->nullable()->index();


            $table->timestamps();

            $table->softDeletes();
            $table->index(['deleted_at']);

            $table->index(['created_at', 'updated_at']);

        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('vh_st_settings');
    }
}
