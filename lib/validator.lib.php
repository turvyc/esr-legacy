<?php

// The server-side validation of user-submitted forms. Takes care of such exciting stuff
// as field completion, username/password length and uniqueness, and email validity.
// If anything is wrong, a ValidationException is thrown. Otherwise, nothing happens.

require_once('exceptions.lib.php');

class Validator {

    const RECAPTCHA_KEY = '6LfntsMSAAAAAMAUj-mMo-OYcuHK49AKqsEg3GEW';

    const MIN_PASSWORD_LENGTH = 3;
    const MIN_USERNAME_LENGTH = 3;
    const MAX_USERNAME_LENGTH = 24;
    const MAX_STRING_LENGTH = 2048; // A nice, round number

    public function verify_recaptcha() {

        require_once('recaptcha.lib.php');

        $recaptcha = recaptcha_check_answer(Validator::RECAPTCHA_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

        if ( ! $recaptcha->is_valid) {
            throw new ValidationException(ValidationException::INVALID_RECAPTCHA);
        }
    }

    // Makes sure all the fields were filled out
    public function verify_completed($array) {

        $args = func_get_args();

        foreach ($args as $arg) {

            $arg = (isset($_POST[$arg])) ? $_POST[$arg] : null;

            if ( $arg == '' || $arg == null ) {
                throw new ValidationException(ValidationException::EMPTY_FIELD);
            }
        }
    }


    // Checks an that an email is valid by comparing it to a ridiculous regex
    public function verify_email($email) {

        if (! preg_match("/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/", $email) ) {
            throw new ValidationException(ValidationException::INVALID_EMAIL);
        }
    }

    // Checks whether the given email has already been used to register
    public function verify_email_unique($email) {
        require('db-connect.php');

        $STH = $DBH->prepare('SELECT id FROM members WHERE email=?');
        $STH->execute(array($email));
        $DBH = null;

        if ($STH->fetch()) {
            throw new ValidationException(ValidationException::USERNAME_TAKEN);
        }
    }

    // Makes sure a password was entered correctly and is of sufficient length
    public function verify_password($password, $repeat) {

        if ($password != $repeat) {
            throw new ValidationException(ValidationException::PASSWORDS_DONT_MATCH);
        }

        if (! $this->check_length($password, Validator::MIN_PASSWORD_LENGTH)) {
            throw new ValidationException(ValidationException::PASSWORD_TOO_SHORT);
        }
    }


    // Makes sure the username is the correct length and doesn't contain any
    // illegal characters.
    public function verify_username($username) {

        if (! $this->check_length($username, Validator::MIN_USERNAME_LENGTH, Validator::MAX_USERNAME_LENGTH)) {
            throw new ValidationException(ValidationException::USERNAME_LENGTH_ERROR);
        }

        if (preg_match("/[£!@#$%^&*()+={}[\]\\';,.\/\"|:<>?~]/", $username)) {
            throw new ValidationException(ValidationException::INVALID_CHAR);
        }

        require('db-connect.php');

        $STH = $DBH->prepare('SELECT id FROM members WHERE username=?');
        $STH->execute(array($username));
        $DBH = null;

        if ($STH->fetch()) {
            throw new ValidationException(ValidationException::USERNAME_TAKEN);
        }
    }

    // Verifies that a string is within a certain length.
    private function check_length( $string, $min, $max = Validator::MAX_STRING_LENGTH ) {
        return (strlen($string) >= $min && strlen($string) <= $max);
    }
}
