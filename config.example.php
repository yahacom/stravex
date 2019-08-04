<?php
/*
 * Database connection
 * */
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

/*
 * Strava application credentials
 * */
define('ST_CLIENT_ID', '');
define('ST_CLIENT_SECRET', '');
define('ST_APP_DOMAIN', '');

define('ST_API_URL', 'https://www.strava.com/api/v3/');
define('ST_OAUTH_ENDPOINT', 'https://www.strava.com/oauth/');

/*
 * Telegram bot
 * */
define('TG_TOKEN', '');
define('TG_API_URL', 'https://api.telegram.org/bot' . TG_TOKEN . '/');

/*
 * Path to upload dir from server root
 * Need to temporary store files downloaded from Strava
 * */
define('UPLOAD_DIR', '');