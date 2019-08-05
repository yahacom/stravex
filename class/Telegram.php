<?php

class Telegram {

    static public function send_message($chat_id, $message, $reply_markup = []) {
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ];
        if (!empty($reply_markup))
            $data['reply_markup'] = $reply_markup;
        self::send_request_webhook('sendMessage', $data);
    }

    static public function send_chat_action($chat_id, $action) {
        $data = [
            'chat_id' => $chat_id,
            'action' => $action
        ];
        self::send_request_webhook('sendChatAction', $data);
    }

    static public function get_keyboard_markup($buttons) {
        $kbd = [];
        foreach ($buttons as $button) {
            $btn = [
                'text' => $button['label'],
                'callback_data' => $button['cb']
            ];
            $kbd[] = [$btn];
        }
        return json_encode(['inline_keyboard' => $kbd]);
    }

    static public function edit_message_text($chat_id, $message_id, $message, $reply_markup = []) {
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'html',
            'disable_web_page_preview' => true
        ];
        if (!empty($reply_markup))
            $data['reply_markup'] = $reply_markup;
        self::send_request_webhook('editMessageText', $data);
    }

    static public function send_document($chat_id, $caption, $document) {
        $data = [
            'chat_id' => $chat_id,
            'caption' => $caption,
            'document' => new CURLFile(realpath($document)),
            'thumb' => new CURLFile(realpath($_SERVER['DOCUMENT_ROOT'] . "/stravex/assets/images/st-gpx.jpg"))
        ];
        return self::send_request('sendDocument', $data, 'POST');
    }

    static private function send_request($call, $data, $method = 'GET') {
        $ch = curl_init();
        if ($call === 'sendDocument')
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type:multipart/form-data"
            ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'GET')
            $call .= '?' . http_build_query($data);
        elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_URL, TG_API_URL . $call);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    static private function send_request_webhook($call, $parameters) {
        $parameters['method'] = $call;
        header("Content-Type: application/json");
        echo json_encode($parameters);
        return;
    }
}