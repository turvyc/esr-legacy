<?php

require_once('../lib/db-connect.php');
require_once('../lib/validator.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/settings.lib.php');
require_once('../utils.php');

$validator = new Validator();
$session = new Session();

try {
    $validator->verify_completed('current', 'email');
    $validator->verify_email($_POST['email']);

    $member = Member::construct_from_email($_POST['email']);

    // One last little check
    if (! $member->get_username() == $session->get_username()) {
        throw new ValidationException('The member and SESSION usernames don\'t match!');
    }
}

catch (ValidationException $exception) {
    $session->set_error($exception->get_user_message());
    header("location:$_URL/user/$session->get_username()");
    exit(1);
}

$current = sha1(Member::SALT . $_POST['current']) . sha1($_POST['current'] . Member::SALT);

try {
    $member->verify_password($_POST['current']);
    $member->set_email($_POST['email']);
} 

catch(MemberException $e) {
    $session->set_error($e->getMessage());
    header("location:$_URL/user/$session->get_username()");
    exit(1);
}

$session->set_info('You have successfully changed your e-mail!');
header("location:$_URL/info");
exit(0);

?>
