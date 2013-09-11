<?php 

/** write.php

Displays the form which allows users to write a new review. If the review is
for an existing school, the school's information is passed to review.hdl.php
as hidden inputs.

*/

require_once('lib/utils.php');
require_once('lib/db-connect.php');
require_once('lib/stars.lib.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');
require_once('lib/school.lib.php');
require_once('lib/review.lib.php');

$session = new Session();

// This dummy function is needed to keep countries.html from complaining
function isSelected($x,$y) { }

// Ensure the user is logged in
if (! $session->is_authorized()) {
    $session->set_info('Before you can rate a school, I\'m afraid you will have 
    to <a href="login">login</a> or <a href="register">register</a>.');
    header("location:$_URL/info");
    exit(0);
} 

// If the id GET parameter is set, the user clicked "write a review for this
// school" in school.php, so we can create a School object and load the form
// with its info.
if (isset($_GET['id'])) {
    try {
        $school = new School($_GET['id']);
    }
    // If the ID wasn't valid, the GET param must have been fiddled with, so
    // unset it and carry on.
    catch(SchoolException $e) {
        if ($_DEBUG) {
            echo $e;
            exit(1);
        }
        unset($_GET['id']);
    }
}

$page = new Page('Write a Review');
$page->render_header();

?>

<div id="midBar">
    <form id="writeReview" name="writeReview" onsubmit="return validateWrite()" 
        action="hdl/write.hdl.php" method="post">
        <ul class="error" id="errorList">
            <li class="error" id="requiredError">Please make sure you have 
            completed each section of the form.</li>
        </ul>

        <?php if (isset($_GET['id'])) { ?>

        <h2>Write a New Review for <?php 
            echo ucwords(html($school->get_name())).' ('.
            ucwords(html($school->get_city())).', '.
            ucwords(html($school->get_country())).')'; ?></h2><br />

        <p class="center">All fields are mandatory!</p><hr /><br />
        <input type="hidden" id="name" value="<?php echo $school->get_name(); ?>" name="name" />
        <input type="hidden" id="type" value="<?php echo $school->get_type(); ?>" name="type" />
        <input type="hidden" id="country" value="<?php echo $school->get_country(); ?>" name="country" />
        <input type="hidden" id="city" value="<?php echo $school->get_city(); ?>" name="city" />

        <?php } else { /* It's a new school, show the full form */ ?>

        <h2>Write a New Review</h2><br />
        <p class="center">All fields are mandatory!</p><hr /><br />

        &nbsp;&nbsp;<label for="name">School Name (in English)</label>
        <a class="toggleHelp" onclick="toggleVisibility('nameHelp')">[?]</a><br />
        <span class="help" id="nameHelp">Enter the full English name of the 
            school (or the Latinization of its name). If it is a branch school, 
            make sure specify exactly which branch it is.</span>
        <input class="wideInput" id="name" type="text" name="name" /><br /><br />

        <select id="type" name="type">
            <option selected value="" disabled="disabled">Select school type</option>
            <option value="kindergarten">Kindergarten</option>
            <option value="elementary school">Elementary School</option>
            <option value="middle school">Middle School</option>
            <option value="high school">High School</option>
            <option value="college">University/College</option>
            <option value="language school">Language School</option>
            <option value="recruiting agency">Recruiting Agency</option>
        </select>

        <select class="select" id="country" name="country">
            <option selected disabled="disabled" value="">Select a Country</option>
            <?php require_once('countries.html'); ?>

            <input type="text" class="inputText" id="city" name="city" 
            onfocus="searchFocus(this)" value="City or town" size="" />
            <a class="toggleHelp" onclick="toggleVisibility('locationHelp')">[?]</a>
            <span class="help" id="locationHelp">Choose the school type which 
                best describes your school, as well as the country and city in 
                which it is located. If it is a chain school, make sure to 
                choose the location of the branch you worked in, not the 
                location of the company headquarters.</span><br /><br />

            <?php } /* Ends the if-else statement */ ?>

            <img class="icon" alt="Workload icon" src="img/workload.png" />
            Workload <a class="toggleHelp" onclick="toggleVisibility('workHelp')">[?]</a>
            <span class="help" id="workHelp">Rate your average weekly workload. 
                How many hours a week did you work? How many students in a 
                class? Did you have to plan your own lessons? How about grading 
                assignments and exams? Were there mandatory office hours?</span>
            <?php 
            $workload_stars = new Stars(Stars::WORK);
            echo $workload_stars->to_html();
            ?>

            <img class="icon" alt="Admin icon" src="img/admin.png" />
            Administration <a class="toggleHelp" onclick="toggleVisibility('adminHelp')">[?]</a>
            <span class="help" id="adminHelp">Rate the administration of your 
                school. Did you get along? Were they professional (i.e. were 
                you payed on time, were there any "surprises" when you arrived, 
                etc.)? Did they communicate well?</span>
            <?php 
            $admin_stars = new Stars(Stars::ADMIN);
            echo $admin_stars->to_html();
            ?>

            <img class="icon" alt="Accom icon" src="img/accom.png" ?>
            Accomodations (No accomodation provided:<input type="checkbox" 
            name="noAccom" id="noAccom" value="" onclick="accomClick(this)"/>)
            <a id="accom" class="toggleHelp" onclick="toggleVisibility('accomHelp')">[?]</a>
            <span class="help" id="accomHelp">Rate the accomodations. Were they 
                provided by the school? Were you alone or did you share? Were 
                they furnished? Were utilities payed? What condition were they 
                in? Were they in a convenient location?</span>
            <?php 
            $accomodation_stars = new Stars(Stars::ACCOM);
            echo $accomodation_stars->to_html();
            ?>

            <img class="icon" alt="Recomp icon" src="img/recomp.png" ?>
            Recompensation <a class="toggleHelp" onclick="toggleVisibility('recompHelp')">[?]</a>
            <span class="help" id="recompHelp">Rate your recompensation. How 
                good was your salary? How many days off a week did you get? 
                How many weeks of vacation did you get a year? Were there 
                any bonuses?</span>
            <?php 
            $recomp_stars = new Stars(Stars::RECOMP);
            echo $recomp_stars->to_html();
            ?>

            The Good <a class="toggleHelp" onclick="toggleVisibility('prosHelp')">[?]</a><br />
            <span class="help" id="prosHelp">Describe the pros, or good things 
                about working for this school. Be as descriptive or verbose as 
                you like. Allowed HTML: &lt;b&gt;, &lt;i&gt;, &lt;strong&gt;, &lt;em&gt;</span>
            <textarea cols="75" rows="8" id="pros" name="pros" /></textarea><br /><br />

            The Bad <a class="toggleHelp" onclick="toggleVisibility('consHelp')">[?]</a><br />
            <span class="help" id="consHelp">Describe the cons, or bad things 
                about working for this school. Be as descriptive or verbose 
                as you like. Allowed HTML: &lt;b&gt;, &lt;i&gt;, &lt;strong&gt;, &lt;em&gt;</span>
            <textarea cols="75" rows="8" id="cons" name="cons" /></textarea><br />
            <input type="submit" value="Submit" />
        </form>
    </div>

<?php $page->render_footer(); ?>
