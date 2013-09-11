<?php

/** 404.php

The 404 page. Not much else to say.

**/

require_once('lib/page.lib.php');
$page = new Page('404');

$page->render_header();
?>

   <h2 style="text-align:center;line-height:150px;">404!</h2>
   <h3>Please <a href="contact">let us know about this problem</a>!</h3>

<?php $page->render_footer(); ?>
