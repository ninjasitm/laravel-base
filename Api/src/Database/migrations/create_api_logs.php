<?php

namespace Nitm\Api\Updates;

use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class CreateRestfulLogs extends Migration
{
    public function up()
    {
        if(!Schema::hasTable('nitm_api_logs')) {
            Schema::create('nitm_api_logs', function ($table) {
                $table->increments('id');
                $table->string('ip')->nullable()->index();
                $table->string('used_key')->nullable();
                $table->text('referer')->nullable();
                $table->text('browser')->nullable();
                $table->text('fullurl')->nullable();
                $table->integer('status_code');
                $table->string('request_method');
                $table->integer('api_status');
                $table->decimal('timepassed', 6, 3);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down()
    {
          Schema::dropIfExists('nitm_api_logs');
    }
}
