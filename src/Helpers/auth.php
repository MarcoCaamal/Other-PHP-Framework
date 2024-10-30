<?php

use SMFramework\Auth\Auth;
use SMFramework\Auth\Authenticatable;

function auth(): ?Authenticatable
{
    return Auth::user();
}
function isGuest(): bool
{
    return Auth::isGuest();
}
