<?php

namespace Nitm\Content\Interactions\PaymentMethod;

use Nitm\Content\Contracts\Interactions\PaymentMethod\RedeemCoupon;

class RedeemStripeCoupon implements RedeemCoupon
{
    /**
     * {@inheritdoc}
     */
    public function handle($billable, $coupon)
    {
        $billable->applyCoupon($coupon);
    }
}