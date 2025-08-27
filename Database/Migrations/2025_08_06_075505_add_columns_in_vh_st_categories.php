<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInVhStCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('vh_st_categories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('slug');
            $table->string('meta_title')->nullable()->index()->after('image_path');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('vh_st_categories', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });
    }
}
