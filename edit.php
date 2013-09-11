<?php

require_once('lib/db-connect.php');
require_once('lib/stars.lib.php');
require_once('lib/settings.lib.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');
require_once('utils.php');

if (!isset($_GET['id'])) {
    header("location:$_URL/404");
    exit(1);
}

$session = new Session();

/* Make sure the requested review exists */
$STH = $DBH->prepare('SELECT * FROM reviews WHERE id=?');
$STH->execute(array($_GET['id']));
$review = $STH->fetchAll();

if (count($review)) {
    $review_id = $review[0]['id'];
    $author = $review[0]['author'];
    $school_id = $review[0]['school'];
    $name = html($review[0]['name']);
    $date = myDate($review[0]['date']);
    $author = html($review[0]['author']);
    $workloadStars = $review[0]['workloadStars'];
    $adminStars = $review[0]['adminStars'];
    $accomStars = $review[0]['accomStars'];
    $recompStars = $review[0]['recompStars'];
    $averageStars = $review[0]['averageStars'];
    $pros = html($review[0]['pros']);
    $cons = html($review[0]['cons']);
}

else {
    header("location:$_URL/404");
    exit(1);
}


// Ensure the user is logged in
if (! $session->is_authorized()) {
    $session->set_info('Before you can do this, I\'m afraid you\'ll have to <a href="login">login</a> or <a href="register">register</a>.');
    header("location:$_URL/info");
    exit(1);
}

// Ensure the user is trying to edit their own review
elseif ($session->get_username() != $author) {
    $session->set_info('So sorry, but you can only edit your own reviews. Nice try though!');
    header("location:$_URL/info");
    exit(1);
}

function checkAccom($x) {
    if (is_null($x)) {
        echo 'checked="checked"';
    }
}

$stars = new Stars();
$page->render_header();
$page = new Page('Editing your review of ' . $name);
?>

<div id="midBar">
    <form name="writeReview" method="post" onsubmit="return validateEdit()" action="hdl/edit.hdl.php">
        <h2>Edit Your Review of <?php echo $name; ?></h2><hr />
        <ul class="error" id="errorList">
            <li class="error" id="requiredError">Please make sure you have completed each section of the form.</li>
        </ul>

        <img class="icon" alt="Workload icon" src="img/workload.png" />Workload <a id="workload" class="toggleHelp" onclick="toggleVisibility('workHelp')">[?]</a>
        <span class="help" id="workHelp">Rate your average weekly workload. How many hours a week did you work? How many students in a class? Did you have to plan your own lessons? How about grading assignments and exams? Were there mandatory office hours?</span>
        <?php $stars->set_rating($workloadStars); echo $stars->show_stars(); ?>

        <img class="icon" alt="Admin icon" src="img/admin.png" />Administration <a id="admin" class="toggleHelp" onclick="toggleVisibility('adminHelp')">[?]</a>
        <span class="help" id="adminHelp">Rate the administration of your school. Did you get along? Were they professional (i.e. were you payed on time, were there any "surprises" when you arrived, etc.)? Did they communicate well?</span>
        <?php $stars->set_rating($adminStars); echo $stars->show_stars(); ?>

        <img class="icon" alt="Accom icon" src="img/accom.png" ?>Accomodations (No accomodation provided:<input type="checkbox" name="noAccom" id="noAccom" <?php checkAccom($accomStars); ?> value="" onclick="accomClick(this)"/>)<a id="accom" class="toggleHelp" onclick="toggleVisibility('accomHelp')">[?]</a>
        <span class="help" id="accomHelp">Rate the accomodations. Were they provided by the school? Were you alone or did you share? Were they furnished? Were utilities payed? What condition were they in? Were they in a convenient location?</span>
        <?php $stars->set_rating($accomStars); echo $stars->show_stars(); ?>

        <img class="icon" alt="Recomp icon" src="img/recomp.png" ?>Recompensation <a id="recomp" class="toggleHelp" onclick="toggleVisibility('recompHelp')">[?]</a>
        <span class="help" id="recompHelp">Rate your recompensation. How good was your salary? How many days off a week did you get? How many weeks of vacation did you get a year? Were there any bonuses?</span>
        <?php $stars->set_rating($recompStars); echo $stars->show_stars(); ?>

        Pros <a class="toggleHelp" onclick="toggleVisibility('prosHelp')">[?]</a><br />
        <span class="help" id="prosHelp">Describe the pros, or good things about working for this school. Be as descriptive or verbose as you like.</span>
        <textarea class="wideInput" id="pros" name="pros"><?php echo html($pros); ?></textarea><br /><br />

        Cons <a class="toggleHelp" onclick="toggleVisibility('consHelp')">[?]</a><br />
        <span class="help" id="consHelp">Describe the cons, or bad things about working for this school. Be as descriptive or verbose as you like.</span>
        <textarea class="wideInput" id="cons" name="cons"><?php echo html($cons); ?></textarea><br />
        <input type="hidden" name="review_id" value="<?php echo $review_id; ?>" />
        <input type="hidden" name="school_id" value="<?php echo $school_id; ?>" />
        <input type="submit" value="Submit" />
    </form>
</div>

<?php $page->render_footer(); ?>
