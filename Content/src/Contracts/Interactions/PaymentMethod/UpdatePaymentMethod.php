<?php

namespace Nitm\Content\Contracts\Interactions\PaymentMethod;

interface UpdatePaymentMethod
{
    /**
     * Update the billable entity's payment method.
     *
     * @param  mixed $billable
     * @param  array $data
     * @return void
     */
    public function handle($billable, array $data);
}
