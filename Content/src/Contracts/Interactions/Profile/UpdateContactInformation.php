<?php

namespace Nitm\Content\Contracts\Interactions\Profile;

interface UpdateContactInformation
{
    /**
     * Get a validator instance for the given data.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param iterable                                     $data
     * @return \Illuminate\Validation\Validator
     */
    public function validator($user, array $data);

    /**
     * Update the user's contact information.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param iterable                                     $data
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function handle($user, array $data);
}
