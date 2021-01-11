<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class HydrateAshisoEnterpriseMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('ashiso_enterprise_enterprises_has_users')
            ->orderBy('created_at')
            ->each(function (object $row) {
                DB::table('ashiso_enterprise_members')->insert([
                    'id' => Uuid::uuid4(),
                    'user_id' => $row->user_id,
                    'enterprise_id' => $row->enterprise_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'deleted_at' => $row->deleted_at,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('ashiso_enterprise_enterprises_has_users')->truncate();
    }
}
