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
        $seeders = [
            'UserSeederTable',
            'ExpertiseSeeder',
            'CategoriesSeeder',
            'ClientsSeeder',
            'MentionsSeeder',
            'PostCategoriesSeeder',
            'PostsCategoriesSeeder',
            'PostsSeeder',
            'ProjectsSeeder',
            'ProjectTypesSeeder',
            'PeopleSeeder',
        ];

        foreach ($seeders as $seeder) {
            $path = __DIR__ . '/' . $seeder . '.php';
            if (file_exists($path)) {
                require_once $path;
            }

            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}