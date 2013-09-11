<?php
require_once('lib/session.lib.php');
require_once('lib/cookie.lib.php');

$session = new Session();
$cookie  = new Cookie();

$session->logout();

try {
    $cookie->destroyCookie();
}
catch(CookieException $error) {
    // Do nothing, for now. 
    // Should have an error message displayed to the user and an automatic
    // error report sent to the webmaster.
}

require('lib/settings.lib.php');
header("location:$_URL");
?>
