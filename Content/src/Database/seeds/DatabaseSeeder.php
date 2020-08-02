<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(ExpertiseSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(ClientsSeeder::class);
        $this->call(MentionsSeeder::class);
        $this->call(PostCategoriesSeeder::class);
        $this->call(PostsCategoriesSeeder::class);
        $this->call(PostsSeeder::class);
        $this->call(ProjectsSeeder::class);
        $this->call(ProjectTypesSeeder::class);
        $this->call(PeopleSeeder::class);
    }
}