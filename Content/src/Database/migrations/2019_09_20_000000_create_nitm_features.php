<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNitmFeatures extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_features')) {
            Schema::create(
                'nitm_features', function ($table) {
                    $table->engine = 'InnoDB';
                    $table->increments('id');
                    $table->string('title', 140);
                    $table->string('slug', 140);
                    $table->string('description', 140);
                    $table->integer('type_id')->nullable();
                    $table->timestamp('created_at')->nullable();
                    $table->integer('author_id')->nullable();
                    $table->timestamp('updated_at')->nullable();
                    $table->integer('editor_id')->nullable();
                    $table->boolean('is_active')->default(0);
                    $table->softDeletes();

                    \Nitm\Content\Models\Category::create(
                        [
                        'title' => 'Feature Type',
                        'slug' => 'feature-type',
                        'description' => 'Feature types',
                        'author_id' => null,
                        'editor_id' => null
                        ]
                    );
                }
            );
        }
    }

    public function down()
    {
        //   Schema::dropIfExists('nitm_features');
    }
}