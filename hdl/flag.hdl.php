<?php

require_once('../lib/settings.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/review.lib.php');
require_once('../lib/phpmailer.lib.php');
require_once('../lib/utils.php');

if (! isset($_POST['id'])) {
    header("location: $_URL/index");
    exit;
}

try {
    $session = new Session();
    $review = new Review($_POST['id']);
    $review->flag();
}
catch Exception($e) {
    if ($_DEBUG) {
        echo $e;
        exit(1);
    }
    $session->set_info($e->getMessage);
    header("location: $_URL/info");
}

// Send an email to the admin, which allows them to either confirm or deny
// the flag action.
$mail = new phpmailer;
$mail->From = 'noreply@eslschoolrater.com';
$mail->FromName = 'ESL School Rater';
$mail->AddAddress($_FLAG_EMAIL);
$mail->Subject = 'Flagged Review';

$mail->Body = "Review ID: {$_POST['id']}\n\n 
Reason: {$_POST['reason']}\n\n 
The review in question: \n\n $review->get_pros() \n\n $review->get_cons()\n\n 
Confirm: $_URL/hdl/confirm_flag.hdl.php?id={$_POST['id']}&verify=$review->get_flag()\n\n 
Deny: $_URL/hdl/confirm_flag.hdl.php?id={$_POST['id']}&verify=0";

$mail->Send();

$session->set_info('Your flag request has been successfully submitted, 
and it will be reviewed as soon as possible. Thank you for helping keep 
ESL School Rater a decent place :)');

header("location: $_URL/info");
exit(0);

?>
