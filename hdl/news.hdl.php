<?php

/** news.hdl.php

Handles the form submission for adding a new news post.

*/

require('../lib/news.lib.php');
require('../lib/session.lib.php');
require_once('../lib/settings.lib.php');

try {
    $session = new Session();
    $news = new News();
    $news->new_post($_POST['newPost']);
}

catch(NewsException $e) {
    $session->set_info($e);
}

header("location:$_URL");

?>
