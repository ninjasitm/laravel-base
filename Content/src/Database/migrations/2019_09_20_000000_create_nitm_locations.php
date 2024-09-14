<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_location')) {
            Schema::create('nitm_location', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title', 32);
                $table->text('description')->nullable();
                $table->string('name', 128)->nullable();
                $table->string('city')->nullable();
                $table->string('zip')->nullable();
                $table->string('country')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->integer('type_id')->nullable();
                $table->string('address')->nullable();
                $table->string('country_code', 3)->nullable();
                $table->string('state_code', 3)->nullable();
                $table->integer('state_id')->nullable();
                $table->integer('country_id')->nullable();
            });
        }
    }

    public function down()
    {
        //   Schema::dropIfExists('nitm_location');
    }
};
