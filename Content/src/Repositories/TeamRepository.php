<?php

namespace Nitm\Content\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Nitm\Content\NitmContent;
use App\Traits\Repository as RepositoryTrait;
use Nitm\Content\Traits\RepositoryProfile;
use Nitm\Content\Events\PaymentMethod\VatIdUpdated;
use Nitm\Content\Events\PaymentMethod\BillingAddressUpdated;
use Nitm\Content\Contracts\Repositories\TeamRepository as TeamRepositoryContract;

class TeamRepository implements TeamRepositoryContract
{
    use RepositoryTrait;
    use RepositoryProfile;

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return NitmContent::team()->with('owner', 'users')->where('id', $id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function forUser($user)
    {
        return $user->teams()->with('owner')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function create($user, array $data)
    {
        $attributes = [
            'owner_id' => $user->id,
            'name' => $data['name'],
            'trial_ends_at' => Carbon::now()->addDays(NitmContent::teamTrialDays()),
        ];

        if (NitmContent::teamsIdentifiedByPath()) {
            $attributes['slug'] = $data['slug'];
        }

        return NitmContent::team()->forceCreate($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function updateBillingAddress($team, array $data)
    {
        $team->forceFill(
            [
            'card_country' => Arr::get($data, 'card_country'),
            'billing_address' => Arr::get($data, 'address'),
            'billing_address_line_2' => Arr::get($data, 'address_line_2'),
            'billing_city' => Arr::get($data, 'city'),
            'billing_state' => Arr::get($data, 'state'),
            'billing_zip' => Arr::get($data, 'zip'),
            'billing_country' => Arr::get($data, 'country'),
            ]
        )->save();

        event(new BillingAddressUpdated($team));
    }

    /**
     * {@inheritdoc}
     */
    public function updateVatId($team, $vatId)
    {
        $team->forceFill(['vat_id' => $vatId])->save();

        event(new VatIdUpdated($team));
    }
}