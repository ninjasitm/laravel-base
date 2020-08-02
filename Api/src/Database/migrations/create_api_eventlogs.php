<?php

namespace Nitm\Api\Updates;

use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class CreateRestfulEventlogs extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_api_eventlogs')) {
            Schema::create('nitm_api_eventlogs', function ($table) {
                $table->increments('id');
                $table->string('model');
                $table->string('action');
                $table->string('url');
                $table->string('sendmethod');
                $table->integer('model_id')->nullable();
                $table->integer('status')->default(0);
                $table->text('response')->nullable();
                $table->integer('includenew')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_api_eventlogs');
    }
}
