<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_follows')) {
            Schema::create('nitm_follows', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title', 128)->nullable();
                $table->string('type', 32)->default('follow');
                $table->text('followee');
                $table->text('follower');
                $table->integer('follower_id')->nullable();
                $table->integer('followee_id')->nullable();
                $table->dateTime('start_date');
                $table->dateTime('end_date')->nullable();
                $table->boolean('is_admin_action')->nullable();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        //   Schema::dropIfExists('nitm_follows');
    }
};
