<?php

class Client {

    private $access_token;
    private $refresh_token;
    private $expires;
    private $chat_id;
    private $db;
    private $is_client;
    private $name;
    private $strava_id;

    public function __construct($id, $name = '') {
        $this->db = new DB();
        $this->is_client = false;
        $this->name = $name;
        if (empty($id)) return false;

        $this->chat_id = $id;

        $query = "SELECT * from clients where chat_id=$this->chat_id LIMIT 1";
        $response = mysqli_query($this->db->get(), $query);
        if ($response !== false) {
            $record = mysqli_fetch_array($response);
            if (count($record) > 0) {
                $this->chat_id = $record['chat_id'];
                $this->refresh_token = $record['st_auth'];
                $this->access_token = $record['st_access'];
                $this->expires = $record['st_expires'];
                $this->strava_id = $record['st_id'];
                $this->is_client = true;

                if (Strava::is_expired($this->expires)) {
                    $response = Strava::refresh_access_token($this->refresh_token);

                    if (!empty($response->access_token)) {
                        $this->update_client_tokens($response->refresh_token, $response->access_token, $response->expires_at);
                        $this->refresh_token = $response->refresh_token;
                        $this->access_token = $response->access_token;
                        $this->expires = $response->expires_at;
                    }
                }
            }
        }
        return $this;
    }

    static public function add_new_client($chat_id, $refresh_token, $access_token = '', $expires = '', $strava_id) {
        if (empty($chat_id) || empty($refresh_token)) return false;
        $db = new DB();
        $query = "INSERT into clients (chat_id, st_auth, st_access, st_expires, st_id) values ('$chat_id', '$refresh_token', '$access_token', '$expires', '$strava_id')";
        if (mysqli_query($db->get(), $query)) {
            return true;
        } else
            return false;
    }

    public function logout() {
        $db = new DB();
        $query = "DELETE from clients where chat_id='$this->chat_id'";
        if (mysqli_query($db->get(), $query)) {
            return true;
        } else
            return false;
    }

    private function update_client_tokens($refresh_token, $access_token, $expires) {
        $query = "UPDATE clients set st_auth='$refresh_token', st_access='$access_token', st_expires='$expires' where chat_id='$this->chat_id'";
        if (mysqli_query($this->db->get(), $query)) {
            return true;
        } else
            return false;
    }

    public function chat_id() {
        return $this->chat_id;
    }

    public function is_client() {
        return $this->is_client;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_strava_id() {
        return $this->strava_id;
    }

    public function get_access_token() {
        return $this->access_token;
    }
}