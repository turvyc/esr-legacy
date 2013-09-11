<?php

require_once('lib/google.lib.php');
require_once('lib/settings.lib.php');

/**
* The first param is for the redirect. The second is for 
* the association handle. Having it set on null is apparently
* wasteful, but I'm feeling lazy. Fix it later. The third
* param means I want to get the user's email address too.
*/
$google_login = GoogleOpenID::createRequest("$_URL/hdl/google-login.hdl.php", null, true);
$google_login->redirect();

?>
