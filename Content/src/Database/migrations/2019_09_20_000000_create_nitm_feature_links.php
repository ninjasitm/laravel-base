<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_feature_links')) {
            Schema::create('nitm_feature_links', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('remote_type', 128);
                $table->text('remote_class');
                $table->string('remote_table', 128);
                $table->integer('remote_id');
                $table->integer('feature_id');
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        //   Schema::dropIfExists('nitm_feature_links');
    }
};
