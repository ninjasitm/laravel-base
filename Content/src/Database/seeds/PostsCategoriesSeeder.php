<?php

use Flynsarmy\CsvSeeder\CsvSeeder as Seeder;

class PostsCategoriesSeeder extends Seeder
{
    public function __construct()
    {
        $this->table = 'posts_categories';
        $this->filename = base_path() . '/database/seeds/csvs/posts_categories.csv';
    }

    public function run()
    {
        // Recommended when importing larger CSVs
        DB::disableQueryLog();

        // Uncomment the below to wipe the table clean before populating
        DB::table($this->table)->truncate();

        parent::run();
    }
}
