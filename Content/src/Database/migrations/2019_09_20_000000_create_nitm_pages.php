<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNitmPages extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_pages')) {
            Schema::create('nitm_pages', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('page', 64);
                $table->string('modelName', 128);
                $table->string('namespace', 255);
                $table->text('modelClass');
                $table->text('config');
                $table->timestamp('deleted_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->integer('author_id');
                $table->timestamp('updated_at')->nullable();
                $table->integer('editor_id')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_pages');
    }
}