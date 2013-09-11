<?php

/** google-login.hdl.php

Handles logging in with a Google account. The logic handling the actual
interaction with the Google API is in google.lib.php.

*/

require_once('../lib/settings.lib.php');
require_once('../lib/google.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/member.lib.php');
require('../lib/db-connect.php');

$google_login = GoogleOpenID::getResponse();

if (! $google_login->success()) {
    header("location:$_URL/register");
    exit(1);
}

// Extract the username from the email address
$username = substr($email, 0, strpos($email, '@'));
$email = $google_login->email();

// Check if the user already exists in the database by trying to create a 
// Member object using the retrieved username. If a row is returned, just
// log the user in and carry on. If not, add a new user to the database, 
// log them in, and carry on.
try {
    $member = Member::construct_from_username($username);
}
catch(MemberException $e) {
    // Create a bogus password -- the user will use their Google password,
    // but something is still needed in the database.
    $hash = sha1(rand() . time() . Member::SALT);
    $member = Member::create_new_member($username, $hash, $email);
    $member->set_status(Member::ACTIVATED);
    $member->set_activation_key("GOOGLE");
}

// Now log the user in and send them to their profile page
$session = new Session();
$session->login($member->get_username());

header("location:$_URL/user/$member->get_username()");
exit(0);

?>
