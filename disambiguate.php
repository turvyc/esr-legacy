<?php

/** disambiguate.php 

Insert descriptive description here.

*/

require_once('lib/session.lib.php');
require_once('lib/page.lib.php');
require_once('lib/disambiguator.lib.php');

// This page should only be accessible if the user was sent from write.hdl.php, 
// which is the only place that sets the 'similarities' SESSION variable.
if (! isset($_SESSION['similarities'])) {
    header('location:index');
    exit(0);
}

$session = new Session();
$disambiguator = new Disambiguator($_SESSION['similarities']);
$title = $disambiguator->generate_title();
$page = new Page('Disambiguation');
$page->render_header();
?>

<div id="midBar">
    <h1>Disambiguation</h1><br />
    <p>There's a chance that the <?php echo $title; ?> you supplied is another 
    spelling or variation of one of these <?php echo $title; ?> already in the 
    database. If it's not the case, don't worry.</p><br />
    <form class="defaultForm" method="post" action="hdl/disambiguate.hdl.php">
        <?php echo $disambiguator->display_choices(); ?>
        <input type="submit" value="Submit" />
    </form>
</div>

<?php $page->render_footer(); ?>
