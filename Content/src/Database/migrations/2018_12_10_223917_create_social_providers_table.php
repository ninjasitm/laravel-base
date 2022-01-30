<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames  = config('social-auth.table_names');
        $foreignKeys = config('social-auth.foreign_keys');
        $modelName   = config('social-auth.models.user');

        if (!empty($modelName)) {

            // Get users table name
            $userModel = new $modelName;
            $userTable = $userModel->getTable();

            Schema::create($tableNames['social_providers'], function (Blueprint $table) use ($userTable, $tableNames) {
                $table->increments('id');
                $table->string('label');
                if (config('database.default') !== 'testing') {
                    $table->string('slug')->unique()->unsigned()->nullable();
                } else {
                    $table->string('slug')->unsigned()->nullable();
                }
                $table->json('scopes')->nullable();
                $table->json('parameters')->nullable();
                $table->boolean('override_scopes')->default(false);
                $table->boolean('stateless')->default(false);
                $table->timestamps();
            });

            Schema::create(
                $tableNames['user_has_social_provider'],
                function (Blueprint $table) use ($userTable, $tableNames, $foreignKeys) {
                    $table->integer($foreignKeys['users'])->unsigned();
                    $table->integer('social_provider_id')->unsigned();
                    $table->string('token');
                    $table->string($foreignKeys['socials']);
                    $table->timestamp('expires_in')->nullable();

                    $table->foreign($foreignKeys['users'])
                        ->references('id')
                        ->on($userTable)
                        ->onDelete('cascade');

                    $table->foreign('social_provider_id')
                        ->references('id')
                        ->on($tableNames['social_providers'])
                        ->onDelete('cascade');

                    $table->primary([$foreignKeys['users'], 'social_provider_id']);
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
        $tableNames = config('social-auth.table_names');
        if (!empty($tableNames)) {

            Schema::dropIfExists($tableNames['user_has_social_provider']);
            Schema::dropIfExists($tableNames['social_providers']);
        }
    }
}