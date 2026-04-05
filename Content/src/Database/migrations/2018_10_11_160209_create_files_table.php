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
        if (! Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table) {
                $table->increments('id');
                $table->text('entity_type');
                $table->integer('entity_id')->unsigned();
                $table->text('entity_relation');
                $table->text('name')->nullable();
                $table->text('type')->nullable();
                $table->text('fingerprint')->nullable();
                $table->text('readable_size')->nullable();
                $table->integer('size')->unsigned()->nullable();
                $table->text('url')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('files');
    }
};
