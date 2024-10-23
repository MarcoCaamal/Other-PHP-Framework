<?php

use OtherPHPFramework\Session\Session;

function session(): Session {
    return app()->session;
}