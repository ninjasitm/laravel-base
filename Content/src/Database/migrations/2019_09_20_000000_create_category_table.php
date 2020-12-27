<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->text('title')->nullable();
                $table->text('slug')->nullable();
                $table->text('description')->nullable();
                $table->text('photo_url')->nullable();
                $table->integer('author_id');
                $table->integer('editor_id')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->integer('deleter_id')->nullable();
                $table->integer('parent_id')->nullable();
                $table->integer('nest_left')->nullable();
                $table->integer('nest_right')->nullable();
                $table->integer('nest_depth')->nullable();
                $table->softDeletes();

                $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('editor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('deleter_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}