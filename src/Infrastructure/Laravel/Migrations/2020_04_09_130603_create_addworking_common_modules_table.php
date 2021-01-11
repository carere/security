<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAshisoCommonModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ashiso_common_modules', function (
            Blueprint $table
        ) {
            $table->uuid('id');
            $table->uuid('parent_id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();

            $table->primary('id');

            $table
                ->foreign('parent_id')
                ->references('id')
                ->on('ashiso_common_modules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ashiso_common_modules');
    }
}
