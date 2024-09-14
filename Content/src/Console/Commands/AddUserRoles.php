<?php

namespace  Nitm\Content\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Nitm\Helpers\ClassHelper;
use Nitm\Content\Traits\Search;

class AddUserRoles extends Command
{
    protected $signature = 'nitm:add-user-roles
        {--email=* : Search for these emails}
        {--role=* : Add these roles}
        {team : Use this team}';
    protected $description = 'Sync the sequences and primary key id for each table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Finding teams...");
        $team = $this->getTeam();
        if ($this->confirm("Use team {$team->name}?", true)) {
            if (function_exists('setPermissionsteamId')) {
                setPermissionsteamId($team->id);
            }
            $roles = $this->getRoles();
            if (!$roles->count()) {
                $this->error("No roles found");
                return;
            } else {
                $this->info("Preparing to add roles {$roles->pluck('name')} for the following users:");
                $users = $this->getUsers();
                if ($users->count()) {
                    $this->table(['User', 'Exists?'], $users->map(function ($user) {
                        return [$user[0], $user[1] === true ? 'Yes' : 'No'];
                    })->toArray());
                    if ($this->confirm("Is this correct?", true)) {
                        $users->filter(function ($user) {
                            return $user[1] === true;
                        })->map(function ($user) use ($roles) {
                            $user = $user[2];
                            foreach ($roles as $role) {
                                if (!$user->hasRole($role->name)) {
                                    $user->assignRole($role->name);
                                    $this->info("\tAssigning role {$role->name} to {$user->email}");
                                } else {
                                    $this->warn("\tUser {$user->email} already has role {$role->name}");
                                }
                            }
                        });
                    }
                } else {
                    $this->warn("No users found");
                }
            }
        }
    }

    /**
     * Get Roles
     *
     * @return Collection
     */
    public function getRoles(): Collection
    {
        $roles = [];
        $class = config('permission.models.role', \App\Models\Role::class);
        $lowerName = \DB::raw('lower(name)');
        foreach ($this->option('role') as $role) {
            $roles[] = ClassHelper::hasTrait($class, Search::class) ? $class::search($role)->first() : $class::where($lowerName, 'LIKE', "%$role%")->first();
        }
        return collect(array_filter($roles));
    }

    /**
     * Get the team
     *
     * @return Team
     */
    protected function getTeam(): Model
    {
        $class = config('nitm-content.team_model', \App\Models\Team::class);
        $s = $this->argument('team');
        $lowerName = \DB::raw('lower(name)');
        $team = ClassHelper::hasTrait($class, Search::class) ? $class::search($s)->orderBy('name', 'asc')->get() : $class::where($lowerName, 'LIKE', "%$s%")->get();
        if (!$team->count()) {
            $this->error("Team not found");
            exit;
        }

        if ($team->count() > 1) {
            $this->info("Multiple teams found. Please select the right one");
            // $this->table(['ID', 'Name'], $team->map(function ($team) {
            //     return [$team->id, $team->name];
            // }));
            $selection = $this->choice("Please enter the team you'd like to use", $team->pluck('name')->toArray());
            $team = $team->firstWhere('name', $selection);
        } else {
            $team = $team->first();
        }
        return $team;
    }

    /**
     * Get Tables
     *
     * @return collection
     */
    protected function getUsers(): Collection
    {
        $users = [];
        $class = config('nitm-content.user_model', \App\Models\User::class);
        foreach ($this->option('email') as $email) {
            $user = $class::where('email', $email)->first();
            $users[] = [$email, $user instanceof Model, $user];
        }
        return collect($users);
    }
}