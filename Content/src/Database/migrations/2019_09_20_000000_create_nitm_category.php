<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNitmCategory extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_categories')) {
            Schema::create('nitm_categories', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title', 128);
                $table->string('slug', 128);
                $table->text('description');
                $table->text('image_path')->nullable();
                $table->integer('author_id');
                $table->integer('editor_id')->nullable()->default(0);
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->integer('deleter_id')->nullable();
                $table->integer('parent_id')->nullable();
                $table->integer('nest_left')->nullable();
                $table->integer('nest_right')->nullable();
                $table->integer('nest_depth')->nullable();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_categories');
    }
}
