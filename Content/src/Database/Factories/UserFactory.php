<?php
namespace Nitm\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Nitm\Content\Models\User;

class UserFactory extends Factory {
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'name'              => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Include a User for this transaction.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withStripeUserId() {
        return $this->state(function (array $attributes) {
            $stripeFacadeClass = 'CloudCreativity\\LaravelStripe\\Facades\\Stripe';
            $stripeUserClass   = 'Stripe\\User';
            $stripeUser        = null;

            if (class_exists($stripeFacadeClass) && class_exists($stripeUserClass)) {
                $stripeUser = new $stripeUserClass;
                $stripeFacadeClass::fake($stripeUser);
            }

            return [
                'stripe_user_id' => $stripeUser->id ?? null,
            ];
        });
    }
}