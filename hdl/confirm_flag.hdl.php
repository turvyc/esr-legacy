<?php

/** confirm_flag.php

This script handles what happens when an admin either confirms or denies a flag
request by clicking one of the links in the flag email.

*/

require_once('../lib/review.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/settings.lib.php');

$session = new Session();
$review = new Review($_GET['id']);

// If there's no match, there is a chance somebody is plugging in bogus GET values
if ($_GET['verify'] != $review->get_flag()) {
    $session->set_info('Invalid review!');
}

// If the admin denied the flag request, unflag the review
elseif (! $_GET['verify']) {
    $review->update(Review::FLAG, 0);
    $session->set_info('The flag request for review ID ' . $_GET['id'] . ' has been cancelled.');
}

// Otherwise, do nothing except reassure the admin that something happened
else {
    $session->set_info('The flag request for review ID ' . $_GET['id'] . ' has been confirmed.');
}

header("location: $_URL/info");
exit(0);

?>
