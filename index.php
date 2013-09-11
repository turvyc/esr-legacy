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
    <h1>Welcome to ESL School Rater!</h1><br />
    <p>ESL School Rater lets you read and write reviews for ESL/EFL schools 
    around the world. There are a lot of schools out there, and it can be 
    difficult to choose which is the best deal. ESL School Rater helps you 
    with your decision.</p><br />
    <hr />
    <div class="info"> 
        <p style="font-size:200%;text-align:center;">Important notice!</p><br />
        <p>ESL School Rater is still in its infantile stages, and as such 
        needs every review possible.</p><br/>
        <p>Because of this, we have decided that for a limited time, <b>users 
            may write reviews without registering beforehand!</b> Hopefully 
        this encourages more people to write that first review :)</p><br />
        <p style="font-size:200%;text-align:center;"><a class="plain" 
            style="color:#0765A3;" href="write"><b>write a guest review!</a></b></p><br />
        <p style="font-size:75%">Note that you won't be able to edit these reviews. 
        If you want to change something, just contact us.</p>
    </div>
    <hr />
    <div id="news">
        <h2>News</h2>
        <br />
        <?php echo $news->get_news(); ?>
    </div> 
</div> 

<?php $page->render_footer(); ?>
