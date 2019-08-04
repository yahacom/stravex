<?php

class DB {
    private $c;
    function __construct() {
        if (!$c = mysqli_connect(DB_HOST,DB_USER,DB_PASS)) {
            echo 'Database connection error';
            exit;
        }
        if (!mysqli_select_db($c,DB_NAME)) {
            echo 'Database connection error';
            exit;
        }
        mysqli_query($db,"SET NAMES 'utf8'");
        mysqli_query($db,"SET CHARACTER SET 'utf8'");
        $this->c = $c;
        return $this;
    }
    public function get() {
        return $this->c;
    }
}