<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNitmEvents extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_events')) {
            Schema::create('nitm_events', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->boolean('is_free')->default(1);
                $table->string('title');
                $table->string('slug');
                $table->integer('type_id')->nullable();
                $table->integer('category_id')->nullable();
                $table->integer('author_id');
                $table->timestamp('created_at');
                $table->integer('editor_d')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at')->nullable();
                $table->dateTime('postponed_to');
                $table->text('description');
                $table->string('status', 10);
                $table->double('cost', 10, 2)->nullable();
                $table->integer('location_id');
                $table->unique('slug');
                $table->foreign('location_id')->references('id')->on('nitm_location');
                $table->softDeletes();
            });

            \Nitm\Content\Models\Category::create([
                'title' => 'Event Type',
                'description' => 'Event Type',
                'author_id' => 1,
            ]);

            \Nitm\Content\Models\Category::create([
                'title' => 'Event Category',
                'description' => 'Event Category',
                'author_id' => 1,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_events');
    }
}
