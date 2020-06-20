<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddworkingEnterprisesHasModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addworking_enterprises_has_modules', function (
            Blueprint $table
        ) {
            $table->uuid('enterprise_id');
            $table->uuid('module_id');

            $table
                ->foreign('enterprise_id')
                ->references('id')
                ->on('addworking_enterprise_members');

            $table
                ->foreign('module_id')
                ->references('id')
                ->on('addworking_common_modules');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addworking_enterprises_has_modules');
    }
}
