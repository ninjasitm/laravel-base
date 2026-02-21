<?php

namespace Nitm\Content\Interactions\PaymentMethod;

use Nitm\Content\User;
use Nitm\Content\NitmContent;
use Nitm\Content\Contracts\Repositories\UserRepository;
use Nitm\Content\Contracts\Repositories\TeamRepository;
use Nitm\Content\Contracts\Interactions\PaymentMethod\UpdatePaymentMethod;

class UpdateStripePaymentMethod implements UpdatePaymentMethod
{
    /**
     * {@inheritdoc}
     */
    public function handle($billable, array $data)
    {
        // Next, we need to check if this application is storing billing addresses and if so
        // we will update the billing address in the database so that any tax information
        // on the user will be up to date via the taxPercentage method on the billable.
        if (NitmContent::collectsBillingAddress()) {
            NitmContent::call(
                $this->updateBillingAddressMethod($billable),
                [$billable, $data]
            );
        }

        if (! $billable->stripe_id) {
            $billable->createAsStripeCustomer();
        }

        $billable->updateDefaultPaymentMethod($data['stripe_payment_method']);
    }

    /**
     * Get the repository class name for a given billable instance.
     *
     * @param  mixed $billable
     * @return string
     */
    protected function updateBillingAddressMethod($billable)
    {
        return ($billable instanceof User
                    ? UserRepository::class
                    : TeamRepository::class).'@updateBillingAddress';
    }
}
