<?php

require_once('../lib/phpmailer.lib.php');
require_once('../lib/validator.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/exceptions.lib.php');
require_once('../lib/settings.lib.php');
require_once('../lib/db-connect.php');
require_once('../utils.php');

$session = new Session();
$validator = new Validator();

//
try {
    $validator->verify_completed('email');
    $validator->verify_email($_POST['email']);
}

catch (ValidationException $exception) {
    $session->set_error($exception->get_user_message());
    header('location:../forgot');
    exit(1);
}

// Generate a simple random password by choosing a random integer either
// between ASCII A-Z or 0-9.
$password = '';
for ($i = 0; $i < pow(Validator::MIN_PASSWORD_LENGTH, 2); $i++) {
    $password .= (rand() % 2) ? chr(rand(65, 90)) : chr(rand(48, 57)); 
}

$hash = sha1($_SALT . $password) . sha1($password . $_SALT);

try {
    $member = Member::construct_from_email($_POST['email']);
    $member->set_password($hash);
}
catch(MemberException $e) {
    $session->set_error($e->getMessage());
    header('location:../forgot');
    exit(1);
}

// Send the email
$mail = new phpmailer;
$mail->From = 'noreply@eslschoolrater.com';
$mail->FromName = "ESL School Rater";
$mail->AddAddress($_POST['email']);
$mail->Subject = 'ESL School Rater Login Info';
$mail->Body = "Howdy!\r\rAs requested, here is your login information:\r\r
username: $member->get_username()\rpassword: $member->password()\r\r
Mosey on over to $_URL/login to login, and don't forget to change your 
password in your profile!\r\rHasta la vista!\r\rESL School Rater";
$mail->Send();

$info = 'Password successfully reset! Keep a sharp eye out for that e-mail!';
$session->set_info($info);
header('location: ../info');
exit(0);

?>
