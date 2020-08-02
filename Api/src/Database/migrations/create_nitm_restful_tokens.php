<?php

namespace Nitm\Api\Updates;

use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class CreateNitmRestfulTokens extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_api_tokens')) {
            Schema::create('nitm_api_tokens', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id');
                $table->dateTime('expires_at');
                $table->text('permissions')->nullable();
                $table->string('token', 255);
                $table->string('ip', 48);
                $table->text('signature');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_api_tokens');
    }
}