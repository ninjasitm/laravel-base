<?php

use Flynsarmy\CsvSeeder\CsvSeeder as Seeder;

class PostCategoriesSeeder extends Seeder
{
    public function __construct()
    {
        $this->table = 'post_categories';
        $this->filename = base_path() . '/database/seeds/csvs/post_categories.csv';
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
