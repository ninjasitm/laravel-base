<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateMadWebUserHasSocialProviderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_has_social_provider')) {
            Schema::table('user_has_social_provider', function ($table) {
                $table->string('offline_token')->nullable();
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
        if (Schema::hasTable('user_has_social_provider')) {
            Schema::table('user_has_social_provider', function ($table) {
                $table->dropColumn('offline_token');
            });
        }

    }
}
