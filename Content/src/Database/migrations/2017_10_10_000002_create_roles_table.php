<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('roles')) {

            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->string('name');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('role_id');
                $table->string('admin')->nullable();
                $table->string('pivot-update')->nullable();
                $table->text('photo')->nullable();
                $table->string('restricted')->default('Yes');
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
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_roles');
    }
};
