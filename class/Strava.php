<?php

class Strava {

    static public function is_expired($expires) {
        return time() > intval($expires);
    }

    static public function get_access_token($code) {
        $query_params = [
            'client_id' => ST_CLIENT_ID,
            'client_secret' => ST_CLIENT_SECRET,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        return self::send_request('POST', $query_params);
    }

    static public function refresh_access_token($refresh_token) {
        $query_params = [
            'client_id' => ST_CLIENT_ID,
            'client_secret' => ST_CLIENT_SECRET,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token'
        ];

        return self::send_request('POST', $query_params);
    }

    static public function get_routes_list($id, $token, $page = 0) {
        $url = ST_API_URL . "athletes/$id/routes";
        $query_params = [
            'page' => $page + 1,
            'per_page' => 5
        ];
        return self::send_request('GET', $query_params, $url, $token);
    }

    static public function get_route_info($id, $token) {
        $url = ST_API_URL . "routes/$id";
        $response = self::send_request('GET', [], $url, $token);
        return !isset($response->errors) ? $response : false;
    }

    static public function get_route_gpx($id, $token) {
        $url = ST_API_URL . "routes/$id/export_gpx";
        $route = self::get_route_info($id, $token);
        if ($route === false) return false;
        if (self::send_request('GET', [], $url, $token, "$id.gpx"))
            return [
                'info' => $route,
                'path' => UPLOAD_DIR . "/$id.gpx"
            ];
        return false;
    }

    static public function get_auth_uri($chat_id) {
        $query_params = [
            'client_id' => ST_CLIENT_ID,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'redirect_uri' => 'https://' . ST_APP_DOMAIN . '/stravex/auth/' . $chat_id . '/',
            'scope' => 'read_all,profile:read_all,activity:write'
        ];
        return ST_OAUTH_ENDPOINT . 'mobile/authorize?' . http_build_query($query_params);
    }

    static private function send_request($method, $data, $url = '', $token = '', $file = null) {
        $url = empty($url) ? ST_OAUTH_ENDPOINT . 'token' : $url;

        $ch = curl_init();

        if (!empty($token)) {
            $authorization = 'Authorization: Bearer ' . $token;
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$authorization]);
        }
        if ($method === 'GET')
            $url .= '?' . http_build_query($data);
        elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        if ($file !== null) {
            $fp = fopen(UPLOAD_DIR . "/$file", 'w+');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 200);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        $data = json_decode(curl_exec($ch));
        if ($data !== 1 && is_file(UPLOAD_DIR . "/$file"))
            unlink(UPLOAD_DIR . "/$file");
        curl_close($ch);
        return $data;
    }
}