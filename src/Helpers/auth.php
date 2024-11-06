<?php

use LightWeight\Auth\Auth;
use LightWeight\Auth\Authenticatable;

function auth(): ?Authenticatable
{
    return Auth::user();
}
function isGuest(): bool
{
    return Auth::isGuest();
}
