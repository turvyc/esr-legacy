<?php

require_once('../lib/db-connect.php');
require_once('../lib/phpmailer.lib.php');
require_once('../lib/validator.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/settings.lib.php');

$validator = new Validator();
$session = new Session();

try {
    $validator->verify_completed('username', 'password', 'repeat', 'email');
    $session->set_form_data(array('username'=>$_POST['username'], 
        'password'=>$_POST['password'], 'repeat'=>$_POST['repeat'],
        'email'=>$_POST['email']));
    // $validator->verify_recaptcha(); Disabled for legacy version
    $validator->verify_email($_POST['email']);
    $validator->verify_email_unique($_POST['email']);
    $validator->verify_username($_POST['username']);
    $validator->verify_password($_POST['password'], $_POST['repeat']);
}

catch (ValidationException $exception) {
    $session->set_error($exception->getMessage());
    header("location:$_URL/register");
    exit(1);
}

$member = Member::create_new_member($_POST['username'], $_POST['password'], 
$_POST['email']);

// Send the verification email
$key = $member->get_activation_key();
$mail = new phpmailer;
$mail->AddAddress($member->get_email());
$mail->From = "noreply@eslschoolrater.com";
$mail->FromName = "ESL School Rater";
$mail->AddReplyTo("noreply@eslschoolrater.com");
$mail->Subject = 'ESL School Rater Registration';
$mail->Body = "Howdy!\r\rYou, or somebody using this email address, has 
registered at ESLSchoolRater.com. To complete the registration, please click 
on this link:\r\r$_URL/activate?key=$key\r\r
If this is an error, please disregard this message and find the person who has 
been using your email without permission :)\r\rAll the best!\r\rESL School Rater";
$mail->Send();

$session->set_info('You have successfully registered! You should be getting an 
activation e-mail shortly.');

header("location:$_URL/info");
exit(0);

?>
