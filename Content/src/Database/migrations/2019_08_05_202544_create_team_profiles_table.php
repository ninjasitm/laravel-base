<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('team_id')->unsigned()->nullable();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index(['team_id']);
            $table->text('bio')->nullable();
            $table->text('tagline')->nullable();
            $table->timestamps();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->integer('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('geo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_profiles');

        Schema::table('teams', function (Blueprint $table) {
            if (config('database.default') !== 'testing') {
                $table->dropForeign(['city_id']);
            }
            $table->dropColumn('city_id');
        });
    }
};
