<?php
include('../include.php');
$error = false;
if (isset($_REQUEST['code']) && !empty($_REQUEST['code'])) {
    $chat_id = $_REQUEST['ID'];
    $code = $_REQUEST['code'];
    $auth_response = Strava::get_access_token($code);
    $client = new Client($chat_id);
    if ($client->is_client())
        $error = true;
    if (isset($auth_response->errors))
        $error = true;
    else if (!Client::add_new_client(
            $chat_id,
            $auth_response->refresh_token,
            $auth_response->access_token,
            $auth_response->expires_at,
            $auth_response->athlete->id))
            $error = true;

    if (!$error)
        Telegram::send_message(
            $client->chat_id(),
            'You successfully connect your Strava account ' . $auth_response->athlete->firstname . ' ' . $auth_response->athlete->lastname . '.' . "\n\n" .
            'Now you can use the following functions:',
            Telegram::get_keyboard_markup(Stravex::get_main_menu()));
} else
    $error = true;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Stravex - Authentication</title>
    <style>
        @font-face {
            font-family: "Rams Black";
            src: url("/stravex/auth/assets/assets/fonts/Rams-W01-Black.eot");
            src: url("/stravex/auth/assets/fonts/Rams-W01-Black.eot?#iefix")format("embedded-opentype"),
            url("/stravex/auth/assets/fonts/Rams-W01-Black.woff") format("woff"),
            url("/stravex/auth/assets/fonts/Rams-W01-Black.ttf") format("truetype"),
            url("/stravex/auth/assets/fonts/Rams-W01-Black.svg#RamsW01-Black") format("svg");;
            font-style: normal;
            font-weight: normal;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #fc4c02;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: sans-serif;
            font-size: 28px;
        }
        .r-col {
            color: #ffad88;
        }
        .response {
            text-align: center;
        }
        .username {
            margin-bottom: 30px;
        }
        h2 {
            margin: 30px 0;
            font-size: 78px;
            font-family: "Rams Black", sans-serif;
        }
        @media (max-width: 450px) {
            body {
                font-size: 22px;
            }
            h2 {
                font-size: 55px;
            }
        }
    </style>
</head>
<body>
    <div class="response">
        <?php if (!$error) { ?>
            <h2>STRAV<span class="r-col">EX</span></h2>
            <p>connected to</p>
            <h2>STRAVA</h2>
            <div class="username"><?=$auth_response->athlete->firstname . ' ' . $auth_response->athlete->lastname?></div>
        <?php } else { ?>
            <p>Something goes wrong, please try again.</p>
        <?php } ?>
    </div>
</body>
</html>