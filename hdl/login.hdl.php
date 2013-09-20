<?php

require_once('../lib/session.lib.php');
require_once('../lib/validator.lib.php');
require_once('../lib/settings.lib.php');
require_once('../lib/utils.php');

$validator = new Validator();
$session = new Session();

try {
    $validator->verify_completed('username', 'password');
}

catch (ValidationException $e) {
    if ($_DEBUG) {
        echo $e;
        exit(1);
    }

    $session->set_error($e->getMessage());
    header("$_URL/location:login");
    exit(1);
}

// Try to log in.
try {
    $session->attempt_login($_POST['username'], $_POST['password']);
}
catch (SessionException $e) {
    $session->set_error($e->getMessage());
    header("location:$_URL/login");
    exit(1);
}


// Handle the cookie, if applicable.
if (isset($_POST['remember'])) {
    require_once('../lib/cookie.lib.php');
    $cookie = new Cookie();

    try {
        $cookie->saveCookie($_POST['username']);
    }
    catch(CookieException $e) {
        if ($_DEBUG) {
            echo $e;
            exit(1);
        }

        $session->set_error("<h3 class='info'>$e->getMessage()</h3>");
        header("location:$_URL/login");
        exit(1);
    }
}

header("location:$_URL/user/".$_POST['username']);

?>
