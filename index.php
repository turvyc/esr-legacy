<?php

require_once('lib/news.lib.php');
require_once('lib/session.lib.php');
require_once('lib/school.lib.php');
require_once('lib/page.lib.php');
require_once('lib/stars.lib.php');

$session = new Session();
$page = new Page('Home');
$news = new News();
$page->render_header();

?>

<div id="leftBar">
    <h3>Top Rated Schools</h3><br />
    <ol id="top">
        <?php 
        foreach (School::get_top_schools(5) as $school) {
            $stars = new Stars($school->get_name());
            $stars->set_value($school->get_average_rating());
            $stars->set_adjustable(false);
            echo "<li><a href='$_URL/school/{$school->get_url()}'>" . 
                ucwords($school->get_name()) . "</a></li>";
            echo "<span class='small'>{$school->get_city()}, {$school->get_country()}</span>";
            echo $stars->to_html();
        }
        ?>
    </ol>
</div>

<div id="midBar">
    <h1>ESL School Rater, the Legacy Version</h1><br />
    <hr />
    <p>ESL School Rater was my first major development project, beginning
    as a way to improve my HTML and CSS, and growing into a fully-featured
    dynamic website.</p>
    <br />
    <h2><a href="https://github.com/turvyc/esr-legacy">View the Source Code</a><h2>
    <hr />
</div> 

<?php $page->render_footer(); ?>
