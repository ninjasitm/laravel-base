<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_activity')) {
            Schema::create('nitm_activity', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('title', 128);
                $table->string('verb', 32);
                $table->timestamp('created_at')->nullable();
                $table->text('actor')->nullable();
                $table->text('object')->nullable();
                $table->text('target')->nullable();
                $table->boolean('is_admin_action')->nullable()->default(0);
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_activity');
    }
};
