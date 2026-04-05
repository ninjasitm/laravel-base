<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('metadata')) {
            Schema::create('metadata', function (Blueprint $table) {
                $table->increments('id');
                $table->text('entity_type');
                $table->integer('entity_id')->unsigned();
                $table->string('entity_relation')->nullable();
                $table->integer('priority')->nullable();
                $table->text('name');
                $table->text('section')->nullable();
                $table->text('type');
                $table->text('value')->nullable();
                $table->json('options')->nullable();
                $table->timestamps();

                $table->index(['priority']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metadata');
    }
};
