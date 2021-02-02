<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\SocialProvider;

class UpdateMadWebAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_names = config('social-auth.table_names');

        // Get teams table name
        $teamModel = new App\Team;
        $teamTable = $teamModel->getTable();

        Schema::create(
            'team_has_social_provider',
            function (Blueprint $table) use ($teamTable, $table_names) {
                $table->integer('team_id')->unsigned();
                $table->integer('social_provider_id')->unsigned();
                $table->string('token');
                $table->string('offline_token')->nullable();
                $table->string('social_id');
                $table->timestamp('expires_in')->nullable();

                $table->foreign('team_id')
                    ->references('id')
                    ->on($teamTable)
                    ->onDelete('cascade');

                $table->foreign('social_provider_id')
                    ->references('id')
                    ->on($table_names['social_providers'])
                    ->onDelete('cascade');

                $table->primary(['team_id', 'social_provider_id']);
            }
        );
        //Create initial social providers.
        // SocialProvider::firstOrCreate(['slug' => 'google', 'label' => 'Google']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('team_has_social_provider');
    }
}
