<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAshisoEnterpriseMembersTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('ashiso_enterprise_members');

        Schema::create('ashiso_enterprise_members', function (
            Blueprint $table
        ) {
            $table->uuid('id');
            $table->uuid('enterprise_id');
            $table->uuid('user_id');
            //$table->string('job_title')->default('A renseigner');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->index(['enterprise_id', 'user_id'], 'unique_member');

            $table
                ->foreign('enterprise_id')
                ->references('id')
                ->on('ashiso_enterprise_enterprises');

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('ashiso_user_users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ashiso_enterprise_members');
    }
}
