<?php

namespace Nitm\Content;

class NitmContent
{
    use Configuration\CallsInteractions,
        Configuration\ManagesApiOptions,
        Configuration\ManagesAppDetails,
        Configuration\ManagesAppOptions,
        Configuration\ManagesAvailablePlans,
        Configuration\ManagesAvailableRoles,
        Configuration\ManagesBillingProviders,
        Configuration\ManagesModelOptions,
        Configuration\ManagesSupportOptions,
        Configuration\ManagesTwoFactorOptions,
        Configuration\ProvidesScriptVariables;

    /**
     * The NitmContent version.
     */
    public static $version = '1.0.0';
}