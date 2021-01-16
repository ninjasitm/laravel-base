<?php

namespace Nitm\Content\Configuration;

use Nitm\Content\NitmContent;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Auth;
use Nitm\Content\Contracts\InitialFrontendState;

trait ProvidesScriptVariables
{
    /**
     * Get the default JavaScript variables for NitmContent.
     *
     * @return array
     */
    public static function scriptVariables()
    {
        return [
            'translations' => static::getTranslations() + ['teams.team' => trans('teams.team'), 'teams.member' => trans('teams.member')],
            'cardUpFront' => NitmContent::needsCardUpFront(),
            'collectsBillingAddress' => NitmContent::collectsBillingAddress(),
            'collectsEuropeanVat' => NitmContent::collectsEuropeanVat(),
            'createsAdditionalTeams' => NitmContent::createsAdditionalTeams(),
            'csrfToken' => csrf_token(),
            'currency' => config('cashier.currency'),
            'currencyLocale' => config('cashier.currency_locale'),
            'env' => config('app.env'),
            'roles' => NitmContent::roles(),
            'state' => NitmContent::call(InitialFrontendState::class.'@forUser', [Auth::user()]),
            'stripeApiVersion' => Cashier::STRIPE_VERSION,
            'stripeKey' => config('cashier.key'),
            'cashierPath' => config('cashier.path'),
            'teamsPrefix' => NitmContent::teamsPrefix(),
            'teamsIdentifiedByPath' => NitmContent::teamsIdentifiedByPath(),
            'userId' => Auth::id(),
            'usesApi' => NitmContent::usesApi(),
            'usesTeams' => NitmContent::usesTeams(),
            'usesStripe' => NitmContent::billsUsingStripe(),
            'chargesUsersPerSeat' => NitmContent::chargesUsersPerSeat(),
            'seatName' => NitmContent::seatName(),
            'chargesTeamsPerSeat' => NitmContent::chargesTeamsPerSeat(),
            'teamSeatName' => NitmContent::teamSeatName(),
            'chargesUsersPerTeam' => NitmContent::chargesUsersPerTeam(),
            'chargesTeamsPerMember' => NitmContent::chargesTeamsPerMember(),
        ];
    }

    /**
     * Get the translation keys from file.
     *
     * @return array
     */
    private static function getTranslations()
    {
        $translationFile = resource_path('lang/'.app()->getLocale().'.json');

        if (! is_readable($translationFile)) {
            $translationFile = resource_path('lang/'.app('translator')->getFallback().'.json');
        }

        return json_decode(file_get_contents($translationFile), true);
    }
}
