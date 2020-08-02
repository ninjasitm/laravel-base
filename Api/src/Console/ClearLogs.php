<?php

namespace Nitm\Api\Console;

use Db;
use Nitm\Api\Models\Logs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearLogs extends Command
{
    /**
     * @var string The console command name
     */
    protected $name = 'nitm:clearlogs';

    /**
     * @var string The console command description
     */
    protected $description = 'Delete RESTful API Logs';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function fire()
    {
        /* All options */
        $options = $this->option();
        $arguments = $this->argument();

        if ($options['before'] == 'all') {
            try {
                if (!Logs::truncate()) {
                    throw new \Exception();
                } else {
                    $this->info('Deleted ALL Logs');
                    //$this->output->writeln('Deleted ALL Logs');
                }
            } catch (\Exception $e) {
                $this->error($e->getTraceAsString());
            }
        } else {
            $deleteBefore = \DateTime::createFromFormat('mdY', $options['before'])->format('Y-m-d');

            /*
             * TODO: Laravel Model not deleting proper results
             * Possibly this happening because model sends where value as 2015-06-10
             * But needed to build query value as string '2015-06-10' to properly mysql compare
             */

            /*
            $logs = new Logs;
            $deletedAmount = $logs->where('created_at', '=', '2015-06-10')->count();
            $logs->delete();
            */

            $this->line("DELETE FROM `nitm_api_logs` WHERE `created_at` < '$deleteBefore' \r\n");
            $deletedAmount = Db::statement("DELETE FROM `nitm_api_logs` WHERE `created_at` < '$deleteBefore' ");

            $this->info("Deleted Logs before: $deleteBefore (YYYY-mm-dd) \r\nDeleted Logs Amount: $deletedAmount");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //['before', InputArgument::REQUIRED, 'Delete all logs or before a specific date.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['before', null, InputOption::VALUE_OPTIONAL, 'Delete all logs.', 'all'],
        ];
    }
}
