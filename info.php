<?php

require_once('lib/page.lib.php');
require_once('lib/session.lib.php');
require_once('lib/settings.lib.php');

$session = new Session();

try {
    $info = $session->get_info();
}
catch(SessionException $message) {
    if ($_DEBUG) {
        echo $message;
    }
    else {
        header("location:$_URL");
    }
    exit(1);
}

$page = new Page('Just letting you know');
$page->render_header();

?>

<div id="midBar">
    <br /><br />
    <h3 class="info"><?php echo $info; ?></h3>
    <br /><br />
</div>

<?php $page->render_footer(); ?>
