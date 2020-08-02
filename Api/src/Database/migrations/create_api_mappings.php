<?php

namespace Nitm\Api\Updates;

use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Updates\Migration;

class CreateRestfulMappings extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nitm_api_mappings')) {
            Schema::create('nitm_api_mappings', function ($table) {
                $table->increments('id');
                $table->string('reqparameter')->index();
                $table->string('relatedtable');
                $table->string('responsefields');
                $table->integer('read_only')->default(1);
                $table->integer('allow_indexkeys')->default(1);
                $table->integer('result_limit')->default(0);
                $table->string('order_by')->default('ASC');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('nitm_api_mappings');
    }
}
