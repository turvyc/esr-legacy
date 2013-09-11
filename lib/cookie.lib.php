<?php

/** cookie.lib.php

Provides a relatively secure mechanism for persistent login ("remember me"
functionality).

The basic algorithm is as follows:
    1. When a user logs in with "Remember Me" checked, a login cookie is
       generated, consisting of a username/hash pair.
    2. The username/hash pair are stored into the Cookies table of the DB.
    3. When a non-logged-in user visits the site and presents a login cookie,
       the username and hash are looked up in the DB.
    4. If the pair is present, the user is logged in. The old username/hash
       pair are deleted from the database and a new pair is generated, which
       is then both stored in the DB and re-issued to the user as a cookie.

This allows for a single user to have multiple remembered logins from different
browsers or computers.

Algorithm: fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice
Next-level algorithm: jaspan.com/improved_persistent_login_cookie_best_practice
    (to be implemented another time)

**/

require_once('exceptions.lib.php');

class Cookie {

    const COOKIE_NAME = 'logged_in';
    const PATH = '/';
    const TWO_WEEKS = 1209600;  // The number of seconds in 2 weeks

    private $username;
    private $expiry;
    private $hash;

    // Initializes the cookie and sets the expiry date.
    public function __construct() {
        $this->expiry = time() + Cookie::TWO_WEEKS;
    }

    // Sets a cookie containing a username/hash pair, which are loaded into a 
    // database table for later verification.
    public function saveCookie($username) {
        require('db-connect.php');

        $this->username = $username;
        $this->hash = $this->getHash($username);

        $cookie_value = $this->getCookieValue($this->username, $this->hash);

        try {
            $STH = $DBH->prepare("INSERT INTO cookies (username, hash) VALUES (?, ?)");
            $STH->execute(array($this->username, $this->hash));
            $DBH = null;

        }
        catch(PDOException $e) {
            // Log this, one day.
            throw new CookieException(CookieException::COOKIE_NOT_SET);
        }

        if (! setcookie(Cookie::COOKIE_NAME, $cookie_value, $this->expiry, Cookie::PATH)) {
            throw new CookieException(CookieException::COOKIE_NOT_SET);
        }
    }

    public function exists() {
        return isset($_COOKIE[Cookie::COOKIE_NAME]);
    }

    // Checks the user's cookie against the pair in the database.
    public function getUsername() {
        require('db-connect.php');

        $cookie = $this->getCookie();

        $STH = $DBH->prepare('SELECT * FROM cookies WHERE username=? AND hash=?');
        $STH->execute(array($cookie['username'], $cookie['hash']));
        $DBH = null;

        if (! $STH->fetch()) {
            throw new CookieException(CookieException::COOKIE_NOT_FOUND);
        }

        return $cookie['username'];
    }


    /* The cookie's hash must be updated every time it is used to log in */
    public function updateCookie() {
        require('db-connect.php');

        $cookie = $this->getCookie();
        $new_hash = $this->getHash($username); // Update the hash

        $cookie_value = $this->getCookieValue($cookie['username'], $new_hash);

        try {
            $STH = $DBH->prepare('UPDATE cookies SET hash=? WHERE username=? AND hash=?');
            $STH->execute(array($new_hash, $cookie['username'], $cookie['hash']));
            $DBH = null;

            setcookie(Cookie::COOKIE_NAME, $cookie_value, $this->expiry, Cookie::PATH);
        } 
        catch(PDOException $e) {
            // Log this, one day.
        }
    }


    /* Destroys the current cookie. Should be called when a user clicks 
    "logout." However, this doesn't delete all the user's cookies: they may
    have logged in on another computer and still want to be remembered there. */
    public function destroyCookie() {
        require('db-connect.php');

        $cookie = $this->getCookie();

        $this->hash = $cookie['hash'];

        try {
            $STH = $DBH->prepare('DELETE FROM cookies WHERE hash=?');
            $STH->execute(array($this->hash));
            $DBH = null;

            // Delete the cookie from the user's computer by setting
            // an identical cookie, but with the expirty date long past.
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - Cookie::TWO_WEEKS,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

        }
        catch(Exception $error) {
            throw new CookieException("There was an error deleting the cookie: " . $error);
        }
    }


    // Parses the cookie into the username and the hash and returns an 
    // associative array.
    private function getCookie() {

        // Ensure that there is indeed a cookie set
        if (! $this->exists()) {
            throw new CookieException("There is no cookie set.");
        }

        list($username, $hash) = explode('_', $_COOKIE[Cookie::COOKIE_NAME]);
        return array('username'=>$username, 'hash'=>$hash);
    }


    // Returns a sha1 hash of the concatenation of $seed and the system time.
    private function getHash($seed) {
        return sha1($seed . time());
    }


    // Simply concatenates the username and hash with an underscore.
    private function getCookieValue($username, $hash) {
        return $username . '_' . $hash;
    }

}
