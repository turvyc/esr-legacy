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
<h2>Key things I learned</h2>
<ul>
<br />
<li><b>The importance of documentation and version control.</b> 
I learned this one the hard way, several times. I won't get into the details, but suffice it to say that I am now extremely diligent in this department.</li>
<br />
<li><b>How large programs work.</b> The project went through three major refactors, each one a reflection of what I had been learning:
<ol>
<br />
<li>1. The naive approach, with PHP and HTML freely intermingling, each page a giant mess of your favorite long pasta.</li>
<br />
<li>2. I seperated logic into what I called "pages," "handlers," and "libraries." Later, I realized I had unknowingly implemented an MVC architecture.</li>
<br />
<li>3. I discovered the power of objects, and re-wrote the codebase to take advantage of them.
</ol></li>
<br />
<li><b>How web applications work.</b> I learned a lot about HTTP requests, how to handle user sessions safely, and how to take advantage of popular APIs such as Google.</li>
</ul>
<br />
<p>Of course, looking through my code now involves a lot of groaning and forehead slapping. The database design is an embarrassment (learning about relational database schemas in school was a huge moment for me). But this is a good thing! It's proof to myself that I have learned and progressed substantially.</p>
<br />
<p>I'm happy to leave this project behind for now, but if I ever came back to it I would use it as an opportunity to learn a new framework. I had a lot of fun coding it and learned a lot, but it's time to move on.
    <br />
<br />
    <h2><a href="https://github.com/turvyc/esr-legacy">View the Source Code</a><h2>
    <hr />
</div> 

<?php $page->render_footer(); ?>
