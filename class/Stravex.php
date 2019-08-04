<?php

class Stravex {
    private $request_type;
    private $chat_id;
    private $message_id;
    private $name;
    private $data;
    private $is_bot;

    public function __construct($data) {
        $this->is_bot = false;

        if (isset($data['callback_query'])) {
            $this->request_type = 'callback_query';
            $this->chat_id = $data['callback_query']['message']['chat']['id'];
            $this->message_id = $data['callback_query']['message']['message_id'];

            $this->data = $data['callback_query']['data'];

            $first_name = $data['callback_query']['message']['chat']['first_name'];
            $username = $data['callback_query']['message']['chat']['username'];
        } elseif (isset($data['message'])) {
            $this->request_type = 'message';
            $this->chat_id = $data['message']['chat']['id'];

            $this->data = $data['message']['text'];

            $this->is_bot = $data['message']['from']['is_bot'];

            $first_name = $data['message']['from']['first_name'];
            $username = $data['message']['from']['username'];
        }
        $this->name = !empty($first_name) ? $first_name : $username;

        return $this;
    }

    public function get_chat_id() {
        return $this->chat_id;
    }

    public function get_user_name() {
        return $this->name;
    }

    public function is_bot() {
        return $this->is_bot;
    }

    public function get_command() {
        return $this->data;
    }

    public function get_message_id() {
        return $this->message_id;
    }

    static public function send_routes($chat_id, $message_id, $token, $strava_id, $page = 0) {
        $routes = Strava::get_routes_list($strava_id, $token, $page);
        if (empty($routes)) return;
        if (isset($routes->error)) {
            Telegram::send_message($chat_id, 'Something goes wrong');
        }
        $message = "List of your own routes:\n\n";
        foreach ($routes as $key => $route) {
            $distance = number_format((float)$route->distance / 1000, 2, '.', '');
            $elevation = number_format((float)$route->elevation_gain, 2, '.', '');
            $message .= 1 + $key + $page*5 . '. <a href="https://www.strava.com/routes/' . $route->id . '">' . $route->name . "</a>\n<b>Distance:</b> " . $distance . " km\n<b>Elevation:</b> " . $elevation . " m\n<b>Download:</b> /route_" . $route->id . "\n\n";
        }
        Telegram::edit_message_text(
            $chat_id,
            $message_id,
            $message,
            json_encode(['inline_keyboard' => [[['text'=>'Previous', 'callback_data'=>'prev_'.($page-1)], ['text'=>'Next', 'callback_data'=>'next_'.($page+1)]], [['text'=>'Main menu', 'callback_data'=>'main_menu']]]])
        );
    }

    static public function send_route_gpx($chat_id, $message, $token) {
        Telegram::send_chat_action($chat_id, 'upload_document');
        $route_id = explode('_', $message)[1];
        $route = Strava::get_route_gpx($route_id, $token);
        if ($route === false) return;
        Telegram::send_document($chat_id, $route['info']->name, $route['path']);
        unlink($route['path']);
    }

    static public function send_main_menu() {

    }

    public function send_login() {
        $auth_url = Strava::get_auth_uri($this->chat_id);
        $message = 'Hi, ' . $this->name . '. You should provide access for your Strava account to continue. Please, follow link below to complete Login process.';
        Telegram::send_message(
            $this->chat_id,
            $message,
            json_encode(['inline_keyboard' => [[['text' => 'Login to Strava', 'url' => $auth_url]]]]));
    }

    static public function get_main_menu() {
        return [
            [
                'label' => 'Show list of routes',
                'cb' => 'routes'
            ],
            [
                'label' => 'Logout',
                'cb' => 'logout'
            ]
        ];
    }
    static public function get_main_menu_button() {
        return [
            [
                'label' => 'Main menu',
                'cb' => 'main_menu'
            ]
        ];
    }
}