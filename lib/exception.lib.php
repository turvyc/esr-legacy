<?php

class CustomException extends Exception {

    public function get_user_message() {

        $message = parent::getMessage();

        return "<h3 class='info'>$message</h3><br />";
    }
}
