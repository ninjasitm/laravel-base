<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('team_users')) {
            Schema::create('team_users', function (Blueprint $table) {
                $table->unsignedInteger('team_id');
                $table->unsignedInteger('user_id');
                $table->boolean('is_approved')->default(false);
                $table->text('role')->nullable();

                $table->unique(['team_id', 'user_id']);
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
        Schema::dropIfExists('team_users');
    }
};
