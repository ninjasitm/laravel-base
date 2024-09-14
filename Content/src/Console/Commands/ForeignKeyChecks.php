<?php
namespace  Nitm\Content\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schema;

class ForeignKeyChecks extends Command
{
    protected $signature = 'app:foreign-key-checks
        {--enable : Enable foreign key checks}
        {--disable : Disable foreign key checks}';
    protected $description = 'List when scheduled commands are executed.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('disable')) {
            Schema::disableForeignKeyConstraints();
            $this->info("Disabled foreign key checks");
        } else {
            Schema::enableForeignKeyConstraints();
            $this->info("Enabled foreign key checks");
        }
    }
}
