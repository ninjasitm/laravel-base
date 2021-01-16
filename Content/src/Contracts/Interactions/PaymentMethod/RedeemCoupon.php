<?php

namespace Nitm\Content\Contracts\Interactions\PaymentMethod;

interface RedeemCoupon
{
    /**
     * Redeem a coupon for the given billable entity.
     *
     * @param  mixed  $billable
     * @param  string $coupon
     * @return void
     */
    public function handle($billable, $coupon);
}
