<?php

namespace Nitm\Content\Console\Commands;

use DB;
use Illuminate\Console\Command;

/**
 * Sync Sequences
 *
 * @author Malcolm Paul <malcolm@ninjasitm.com>
 */
class SyncSequences extends Command
{
    protected $signature = 'nitm:sync-sequences {table?*}';
    protected $description = 'Sync the sequences and primary key id for each table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Preparing to update sequences on the following tables");
        $this->table(['Table'], [$this->getTables()]);
        if($this->confirm("Is this correct?")) {
            foreach($this->getTables() as $table) {
                DB::transaction(
                    function () use ($table) {
                        $pkMax = DB::select("SELECT nextval('{$table}_id_seq')")[0]->nextval;
                        $pkNext = DB::select("SELECT MAX(id)+1 as pkNext FROM {$table}")[0]->pknext;
                        $this->info("Sequence on $table is (Expected) $pkMax => (Current) $pkNext");
                        if($pkMax != $pkNext) {
                            DB::select("SELECT setval('{$table}_id_seq', COALESCE((SELECT MAX(id)+1 FROM ${table}), 1), false)");
                            $this->info("Updated sequence on $table from $pkMax to $pkNext");
                        }
                    }, 5
                );
            }
        }
    }

    /**
     * Get Tables
     *
     * @return array
     */
    protected function getTables(): array
    {
        $userTables = $this->argument('table');
        if(is_array($userTables) && !empty($userTables)) {
            return $userTables;
        } else {
            return [
                'deliverables'
            ];
        }
    }
}
