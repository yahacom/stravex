<?php
include('include.php');
$updateData = json_decode(file_get_contents('php://input'), true);

$stravex = new Stravex($updateData);

if ($stravex->is_bot()) return;

$client = new Client($stravex->get_chat_id(), $stravex->get_user_name());

switch ($stravex->get_command()) {
    case '/start':
        if (!$client->is_client())
            $stravex->send_login();
        else Telegram::send_message(
            $client->chat_id(),
            'You already logged in');
        break;
    case '/menu':
        if ($client->is_client())
            Telegram::send_message(
                $client->chat_id(),
                'You can use the following functions:',
                Telegram::get_keyboard_markup(Stravex::get_main_menu()));
        else
            $stravex->send_login();
        break;
    case 'main_menu':
        Telegram::edit_message_text(
            $client->chat_id(),
            $stravex->get_message_id(),
            'You can use the following functions:',
            Telegram::get_keyboard_markup(Stravex::get_main_menu()));
        break;
    case 'routes':
        Stravex::send_routes(
            $client->chat_id(),
            $stravex->get_message_id(),
            $client->get_access_token(),
            $client->get_strava_id());
        break;
    case 'logout':
        if ($client->logout()) {
            $auth_url = Strava::get_auth_uri($client->chat_id());
            Telegram::edit_message_text(
                $client->chat_id(),
                $stravex->get_message_id(),
                'Hi, ' . $client->get_name() . '. You should provide access for your Strava account to continue. Please, follow link below to complete Login process.',
                json_encode(['inline_keyboard' => [[['text' => 'Login to Strava', 'url' => $auth_url]]]
                ]));
        }
        break;
    default:
        if (strpos($stravex->get_command(), 'prev_') === 0) {
            $page = explode('_', $stravex->get_command())[1];
            if ($page < 0) return;

            Stravex::send_routes(
                $client->chat_id(),
                $stravex->get_message_id(),
                $client->get_access_token(),
                $client->get_strava_id(),
                $page);
        } elseif (strpos($stravex->get_command(), 'next_') === 0) {
            $page = explode('_', $stravex->get_command())[1];
            Stravex::send_routes(
                $client->chat_id(),
                $stravex->get_message_id(),
                $client->get_access_token(),
                $client->get_strava_id(),
                $page);
        } elseif (strpos($stravex->get_command(), '/route_') === 0) {
            Stravex::send_route_gpx(
                $client->chat_id(),
                $stravex->get_command(),
                $client->get_access_token());
        } elseif (strpos($stravex->get_command(), 'strava.com/routes/') !== false) {
            $route_id = array_pop(explode('/', rtrim($stravex->get_command(), '/')));
            if (empty($route_id) || !is_numeric($route_id)) return;
            if ($client->is_client())
                Stravex::send_route_gpx(
                    $client->chat_id(),
                    '/route_' . $route_id,
                    $client->get_access_token());
            else
                $stravex->send_login();
        }
}