<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('categories', 'priority')) {
            Schema::table(
                'categories',
                function (Blueprint $table) {
                    $table->integer('priority')->nullable()->default(0);
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!app()->environment('testing')) {
            Schema::table(
                'categories',
                function (Blueprint $table) {
                    $table->dropColumn('priority');
                }
            );
        }
    }
};
