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
    <p>ESL School Rater was my first major web development project, beginning
    as a way to improve my HTML and CSS, and growing into a fully-featured
    dynamic website. The codebase has already metamorphasized twice, each
    refactoring a reflection of my growing abilities, and it is currently
    undergoing a third: I am implementing ESR using a third-party framework,
    <a href="ellislab.com/codeigniter">CodeIgniter</a>.</p>
    <br />
    <p>I had a lot of fun creating this website, and I'm excited to complete
    its next incarnation. In the meantime, however, I'd like to show off
    the website as it once was. Please note that the design and coding, both
    front- and back-end, are not an accurate reflection of my current coding
    abilities. Too much of the code is making me wince (hence the refactoring),
    but there are some points about this code of which I'm proud. I developed 
    my own implementation of the MVC pattern, I set up authentication with three
    third-party APIs, (sorry, but that's disabled on this legacy site), and
    wrote clean, well-commented, and ever-more maintanable code.</p>
    <br />
    <p>This Legacy site was entirely created by me, T. Colin Strong, including
    all images, designs, and coding, with the exception of the stars: the star
    GIFs and Javascript are from a website now sadly defunct. (Though I did 
    comment up the Javascript for my own learning purposes. It was quite
    inscrutable at first.)</p>
    <br />
    <h2><a href="http://bitbucket.org/turvyc/esl-school-rater-legacy/src">View the Source Code</a><h2>
    <hr />
</div> 

<?php $page->render_footer(); ?>
