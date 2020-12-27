<?php

use Illuminate\Database\Seeder;
use Nitm\Content\User;
use Nitm\Content\Role;
use \Illuminate\Support\Facades\Hash;

class UserSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::where([
            'email' => 'secret@secret.com'
        ])->orWhere([
            'email' => 'admin@admin.com'
        ])->delete();

        User::updateOrCreate([
            'name' => 'secret',
            'email' => 'secret@secret.com',
            'password' => Hash::make(bin2hex(random_bytes(16))), // secret
            'role_id' => Role::where('name', 'Super Admin')->first()->id,
            'remember_token' => Str::random(10),
        ]);

        User::updateOrCreate([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make(bin2hex(random_bytes(16))), // secret
            'remember_token' => Str::random(10),
        ]);
    }
}
