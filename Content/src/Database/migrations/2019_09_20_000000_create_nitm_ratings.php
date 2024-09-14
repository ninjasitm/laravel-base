<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_ratings')) {
            Schema::create('nitm_ratings', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('rater_id');
                $table->integer('thing_id');
                $table->boolean('is_admin_action');
                $table->smallInteger('value');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('thing_type', 128);
                $table->text('thing_class');
                $table->unique(['rater_id', 'thing_id', 'thing_type']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_ratings');
    }
};
