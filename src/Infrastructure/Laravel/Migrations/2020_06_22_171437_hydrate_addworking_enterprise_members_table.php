<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class HydrateAddworkingEnterpriseMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('addworking_enterprise_enterprises_has_users')->each(
            function (object $row) {
                DB::table('addworking_enterprise_members')->insert([
                    'id' => Uuid::uuid4(),
                    'user_id' => $row->user_id,
                    'enterprise_id' => $row->enterprise_id,
                ]);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('addworking_enterprise_enterprises_has_users')->truncate();
    }
}
