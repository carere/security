<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddworkingCommonModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addworking_common_modules', function (
            Blueprint $table
        ) {
            $table->uuid('id');
            $table->uuid('parent');
            $table->string('name');
            $table->string('description');
            $table->timestamps();

            $table->primary('id');

            $table
                ->foreign('parent_id')
                ->references('id')
                ->on('addworking_common_modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addworking_common_modules');
    }
}
