<?php

namespace Nitm\Api\Updates;

use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class CreateEventhandlerTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_api_eventhandler')) {
            Schema::create('nitm_api_eventhandler', function ($table) {
                $table->increments('id');
                $table->integer('mappings_id')->unsigned();
                $table->string('model');
                $table->string('action');
                $table->string('url');
                $table->string('sendmethod')->default('curl_post');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::drop('nitm_api_eventhandler');
    }
}
