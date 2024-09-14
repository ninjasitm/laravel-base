<?php

namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    use HasFactory;

    protected $table = "billing_subscriptions";

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['provider_plan'];

    /**
     * Get the "provider_plan" attribute from the model.
     *
     * @return string
     */
    public function getProviderPlanAttribute()
    {
        return $this->stripe_plan;
    }
}
