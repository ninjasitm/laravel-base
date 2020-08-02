<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateNitmLocationList extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_location_list')) {
            Schema::create('nitm_location_list', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title', 255)->change();
                $table->integer('location_id');
                $table->integer('item_id');
                $table->string('item_type', 64)->nullable();
            });
        }
    }

    public function down()
    {
    }
}
