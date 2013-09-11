<?php

/** flag.php

Allows the user to flag an inappropriate review. The review in question is 
displayed to the user, and the user may provide a reason as to why they are
flagging it.

Calls:  flag.hdl.php
_SESSION: info

**/

// Prevent accessing this page without a review id in GET.
if (!isset($_GET['id'])) {
    header("location: $_URL/index");
    exit;
}

require_once('lib/db-connect.php');
require_once('lib/settings.lib.php');
require_once('lib/page.lib.php');
require_once('utils.php');

// Retrieve the review in question for display to the user.
try {
    $STH = $DBH->prepare('SELECT pros, cons FROM reviews WHERE id = ?');
    $STH->execute(array($_GET['id']));
    $result = $STH->fetch();
    if (! $result) {
        $session->set_info('That review doesn\'t seem to exist. Please try again or <a href="contact">let us know</a> about the problem.');
        header("location: $_URL/info");
        exit;
    }
}
catch (PDOException $exception) {
    $session->get_info('There was a database error. Please <a href="contact">let us know</a> about the problem.');
    header("location: $_URL/info");
    exit;
}

$page = new Page('Flag a review');
$page->render_header();

?>

<div id="midBar">
    <h1>Flag a Review</h1><br />
    <p>If you feel that this review contains inappropriate or libelous comments, you may submit it to our staff for review. You may also give a reason as to why you would like the review removed.</p><br />
    <p><b>The review in question:</b></p>
    <blockquote id="flaggedReview"> 
        <?php echo html($result['pros']) . '<br /><br />' . html($result['cons']); ?>
    </blockquote><br />
    <p><b>Reason for removal:</b></p>
    <form class="defaultForm" action="hdl/flag.hdl.php" method="post">
        <input class="wideInput" type="text" name="reason" /><br />
        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
        <input type="submit" value="Flag this review" />
    </form>
</div>

<?php $page->render_footer(); ?>
