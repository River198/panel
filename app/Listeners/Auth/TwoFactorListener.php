<?php

namespace river\Listeners\Auth;

use river\Facades\Activity;
use river\Events\Auth\ProvidedAuthenticationToken;

class TwoFactorListener
{
    public function handle(ProvidedAuthenticationToken $event)
    {
        Activity::event($event->recovery ? 'auth:recovery-token' : 'auth:token')
            ->withRequestMetadata()
            ->subject($event->user)
            ->log();
    }
}
