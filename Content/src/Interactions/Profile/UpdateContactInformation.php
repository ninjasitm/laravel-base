<?php

namespace Nitm\Content\Interactions\Profile;

use Illuminate\Support\Facades\Validator;
use Nitm\Content\Events\Profile\ContactInformationUpdated;
use Nitm\Content\Contracts\Interactions\Profile\UpdateContactInformation as Contract;

class UpdateContactInformation implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function validator($user, array $data)
    {
        return Validator::make(
            $data, [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle($user, array $data)
    {
        $user->forceFill(
            [
            'name' => $data['name'],
            'email' => $data['email'],
            ]
        )->save();

        event(new ContactInformationUpdated($user));

        return $user;
    }
}
