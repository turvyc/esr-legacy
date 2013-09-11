<?php

/** activate.php

While this script really should be hdl/activate.hdl.php, doing so would expose
the full path to the user in the activation link, thus exposing the page's 
implementation. So instead I'll just break the rules a little bit to hide hdl
from the user.

*/

require_once('lib/session.lib.php');
require_once('lib/member.lib.php');
require_once('lib/page.lib.php');
require_once('lib/settings.lib.php');
require_once('lib/utils.php');

$session = new Session();

try {
    $member = Member::construct_from_key($_GET['key']);
    $member->set_status(Member::ACTIVATED);
    $session->set_info($message = 'You have successfully activated your account!
    You may now <a href="login">login</a>.');
}
catch (MemberException $e) {
    $session->set_info('There was an error activating your account. Please 
    <a href="contact">let us know</a> about it.');
}

header("location: $_URL/info");
?>
