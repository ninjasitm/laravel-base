<?php

namespace Nitm\Content\Contracts\Repositories;

use Nitm\Content\Models\Team;

interface TeamRepository
{
    /**
     * Get the team matching the given ID.
     *
     * @param  string|int $id
     * @return \Nitm\Content\Models\Team
     */
    public function find($id);

    /**
     * Get all of the teams for a given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($user);

    /**
     * Create a new team with the given owner.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array                                      $data
     * @return \Nitm\Content\Models\Team
     */
    public function create($user, array $data);

    /**
     * Update the billing address information with the given data.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  array                     $data
     * @return void
     */
    public function updateBillingAddress($team, array $data);

    /**
     * Update the European VAT ID number for the given team.
     *
     * @param  \Nitm\Content\Models\Team $team
     * @param  string                    $vatId
     * @return void
     */
    public function updateVatId($team, $vatId);
}