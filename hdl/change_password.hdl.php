<?php

require_once('../lib/validator.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/settings.lib.php');
require_once('../utils.php');

$validator = new Validator();
$session = new Session();

// Validate the form data.
try {
    $validator->verify_completed('current', 'New', 'repeat');
    $validator->verify_password($_POST['New'], $_POST['repeat']);

    $member = Member::construct_from_username($session->get_username());
    $member->set_password(Member::generate_hash($_POST['New']));
}
// This catch block can catch either a ValidationException or a MemberException,
// but since they are handled in the same way, use the superclass.
catch (Exception $exception) {
    $session->set_error($exception->get_user_message());
    header("location:$_URL/user/{$session->get_username()}");
    exit(1);
}

$session->set_info('Your password has been successfully changed.');
header("location:$_URL/info");
exit(0);

?>
