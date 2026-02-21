<?php

namespace Nitm\Content\Traits\User;

use App\Models\CommunicationToken;

trait CommunicationTokens
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    // public function initializeUserCalendar()
    // {
    //     $this->withCount[] = 'newRsvps';
    // }

    /**
     * Get rsvps
     *
     * @return void
     */
    public function communicationTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CommunicationToken::class, 'user_id', 'id');
    }
}
