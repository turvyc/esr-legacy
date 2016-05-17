<?php

require_once('cookie.lib.php');

class Session {

    const SALT = '%>n*N>DFl@FDuF*Zc.:mM@0M}dZ)3lX|V@ZY},ZMD_?d,~Nc:.Dh6[TY>)NZ&]N';

    // The following constants are used as keys in $_SESSION.
    const AUTHORIZED = 'auth';
    const ERROR = 'error';
    const USERNAME = 'username';
    const INFO = 'info';
    const TITLE = 'title';
    const FORM_DATA = 'form_data';

    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }

        // If the user is not authorized, check if a valid cookie exists on 
        // their system, or set restrictive credentials.
        if ( ! isset($_SESSION[Session::AUTHORIZED]) || 
        ! isset($_SESSION[Session::USERNAME]) ||
        ! $this->is_authorized() ||
        ! $this->get_username()) { 
            $cookie = new Cookie();
            if ($cookie->exists()) {
                try {
                    $username = $cookie->getUsername();
                    $this->login($username);
                }
                catch (CookieException $e) {
                    // Log this, one day
                }
            }
            else {
                $_SESSION[Session::AUTHORIZED] = FALSE;
                $_SESSION[Session::USERNAME] = '';
            }
        }		
    }

    // Boolean. Returns true if the user is logged in.
    public function is_authorized() {
        if (! isset($_SESSION[Session::AUTHORIZED])) {
            $_SESSION[Session::AUTHORIZED] = FALSE;
        }
        return $_SESSION[Session::AUTHORIZED];
    }

    // String. Returns the username of the currently logged in user.
    public function get_username() {
        if (! isset($_SESSION[Session::USERNAME])) {
            $_SESSION[Session::USERNAME] = '';
        }
        return $_SESSION[Session::USERNAME];
    }

    public function set_error($exception) {
        require('settings.lib.php');
        if ($_DEBUG) {
            $_SESSION[Session::ERROR] = $exception;
        }
        else {
            $_SESSION[Session::ERROR] = $exception->getMessage();
        }
    }

    // String. Returns an error message set by a handler, or blank string
    // if no message has been set, then unsets the SESSION key.
    public function get_error() {
        $error = (isset($_SESSION[Session::ERROR])) ? $_SESSION[Session::ERROR] : '';
        unset($_SESSION[Session::ERROR]);
        return $error;
    }

    // Sets the info message to be displayed to the user on info.php.
    public function set_info($message) {
        $_SESSION[Session::INFO] = $message;
    }

    // String. Returns the information message, and unsets the SESSION
    // variable.
    public function get_info() {
        if (! isset($_SESSION[Session::INFO])) {
            throw new SessionException(SessionException::NO_INFO);
        }
        $info = $_SESSION[Session::INFO];
        unset($_SESSION[Session::INFO]);
        return $info;
    }

    // Sets an associative array containing form information, in the case that
    // server-side validation fails. This way, the form information can be 
    // remembered when returning to the form page from the handler.
    public function set_form_data($assoc_array) {
        $_SESSION[Session::FORM_DATA] = $assoc_array;
    }

    // Associative Array. Returns the form data, and unsets the SESSION
    // variable.
    public function get_form_data() {
        if (! isset($_SESSION[Session::FORM_DATA])) {
            return;
        }
        $form_data = $_SESSION[Session::FORM_DATA];
        unset($_SESSION[Session::FORM_DATA]);
        return $form_data;
    }

    // Authenticates a username/password pair.
    public function attempt_login($username, $password) {
        require('db-connect.php');
        $password = $this->getPasswordHash($password);
        try {
            $STH = $DBH->prepare("SELECT id FROM members WHERE username=? 
            AND password=? AND status='a'");
            $STH->execute(array($username, $password));
        }
        catch(PDOException $error) {
            // Log this, one day
            throw new SessionException(SessionException::DATABASE_ERROR);
        }

        // If there is a match:
        if ($STH->fetch()) {
            $this->login($username);
            $DBH = null;
        }
        else {
            $_SESSION[Session::AUTHORIZED] = FALSE;
            $_SESSION[Session::USERNAME] = '';
            $DBH = null;
            throw new SessionException(SessionException::LOGIN_ERROR);
        }
    }

    // Void. Completely logs the user out, destroying all session data.
    public function logout() {
        $_SESSION = array();
        session_destroy();
    }

    // This function should be used with EXTREME CAUTION!
    public function login($username) {
        session_regenerate_id();
        $_SESSION[Session::AUTHORIZED] = TRUE;
        $_SESSION[Session::USERNAME] = $username;
    }

    // String. Returns a very large hash of the password and salt.
    private function getPasswordHash($password) {
        return sha1(Session::SALT . $password);
    }	


}
?>
