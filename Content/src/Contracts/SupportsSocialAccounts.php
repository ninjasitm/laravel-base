<?php

namespace Nim\Content\Contracts;

interface SupportsSocialAccounts
{
    /**
     * Get the social accounts table for the model
     *
     * @return string
     */
    public function getSocialAccountsTable(): string;
}