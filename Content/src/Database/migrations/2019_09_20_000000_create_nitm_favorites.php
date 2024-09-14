<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_favorites')) {
            Schema::create('nitm_favorites', function ($table) {
                $table->engine = 'InnoDB';
                $table->integer('id');
                $table->integer('thing_id');
                $table->string('thing_class', 255);
                $table->string('thing_type', 255);
                $table->integer('user_id');
                $table->timestamp('deleted_at')->nullable();
                $table->primary(['id']);
            });
        }
    }

    public function down()
    {
        //   Schema::dropIfExists('nitm_favorites');
    }
};
