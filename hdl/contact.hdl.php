<?php

/** contact.hdl.php

Validates and processes user feedback submittied via the contact form.

**/

require('../lib/validator.lib.php');
require('../lib/phpmailer.lib.php');
require('../lib/session.lib.php');
require_once('../lib/settings.lib.php');


$session = new Session();
$validator = new Validator();

try {
    $validator->verify_completed('email', 'subject', 'message', 'name');
    $validator->verify_email($_POST['email']);
}

catch (ValidationException $e) {
    if ($_DEBUG) {
        echo $e;
        exit(1);
    }
    $session->set_error($e->getMessage();
    header("location:$_URL/contact");
    exit(1);
}

// Create and send an email to the webmaster
$mail = new phpmailer;
$mail->From = $_POST['email'];
$mail->FromName = $_POST['email'];
$mail->AddAddress($_EMAIL);
$mail->Subject = 'ESL School Rater -- ' . ucwords($_POST['subject']);
$mail->Body = stripslashes($_POST['message']) . "\n\n" . stripslashes($_POST['name']) . "\n" . $_POST['email'];
$mail->Send();

$session->set_info("We've done received your message, and we're much obliged. Thanks so much!");
header('location:../info');
exit(0);

?>
