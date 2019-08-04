<?php
if (file_exists(__DIR__ . '/config.php'))
    require_once __DIR__ . '/config.php';
else die('Credentials error');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/DB.php';
require_once __DIR__ . '/class/Client.php';
require_once __DIR__ . '/class/Strava.php';
require_once __DIR__ . '/class/Telegram.php';
require_once __DIR__ . '/class/Stravex.php';