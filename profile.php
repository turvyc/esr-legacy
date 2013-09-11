<?php 
require_once('lib/review.lib.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');
require_once('lib/member.lib.php');
require_once('lib/exceptions.lib.php');
require_once('lib/settings.lib.php');
require_once('lib/utils.php');

try {
    $session = new Session();
    $member = Member::construct_from_username($_GET[Member::USERNAME]);
} 
catch(MemberException $e) {
    if ($_DEBUG) {
        echo $e;
    }
    else {
        header("location:$_URL/404");
    }
    exit(1);
}

$page = new Page($member->get_username() . "'s Profile");
$page->render_header();
?>

<div id="leftBar">
    <h3>Overview</h3><br />
    <p>Joined <?php echo $member->get_date_joined(); ?></p>
    <p><?php echo $member->get_number_of_reviews().' Review'; 
    if ($member->get_number_of_reviews() != 1) { echo 's'; } ?></p>
    <hr />
    <!-- EDIT PROFILE SECTION -->
    <?php if ($session->is_authorized()) { ?>
    <form class="defaultForm" name="changeEmail" onsubmit="return validateChangeEmail()"
        action="hdl/change_email.hdl.php" method="post">
        <h4>Change E-mail</h4><br />
        <ul class="error" id="errorList">
            <li class="error" id="emailError">Please enter a valid email address.</li>
            <li class="error" id="passwordError">Your password must be at least 6 characters long.</li>
            <li class="error" id="noMatchError">Your passwords don't match!</li>
            <li class="error" id="requiredError">Please fill in all the fields.</li>
        </ul>
        <label for="current">Current password</label>
        <input type="password" name="current" /><br />
        <label for="email">New e-mail:</label>
        <input type="text" name="email" /><br />
        <label for="submitEmail">&nbsp;</label>
        <input type="submit" value="Submit" name="submitEmail" id="submitEmail" /><br />
    </form>
    <form class="defaultForm" name="changePassword" onsubmit="return validateChangePassword()" 
        action="hdl/change_password.hdl.php" method="post"><br />
        <h4>Change Password</h4><br />
        <label for="current">Current password</label>
        <input type="password" name="current" /><br />
        <label for="New">New Password</label>
        <input type="password" name="New" id="New" />
        <label for="repeat">Repeat New Password</label>
        <input type="password" name="repeat" id="repeat" />
        <label for="submitPass">&nbsp;</label>
        <input type="submit" value="Submit" name="submitPass" id="submitPass" /><br />
    </form>
    <hr />

    <?php } ?>
</div> <!-- Closes leftBar -->

<div id="midBar">
    <!-- MY REVIEWS SECTION -->
    <h1><?php echo $member->get_possessive_username($session->get_username()); ?> Reviews</h1>
    <?php echo $session->get_error();
    // Make myself a little admin spot here for news updates!
    if ($session->get_username() == 'turvyc' && $member->get_username() == 'turvyc') { ?>
    <form class="defaultForm" name="newsForm" method="post" action="hdl/news.hdl.php">
        <label for="newPost">New Post</label><br />
        <textarea tabindex="1" cols="75" rows="8" name="newPost"></textarea><br />
        <input type="hidden" value="1" name="submitted" />
        <input type="submit" value="Publish" />
    </form><br /> <?php }
    $review_ids = $member->get_review_ids();
    if (! $review_ids) {
        echo '<br /><h3>There doesn\'t seem to be anything here.</h3><br />';
        if ($session->is_authorized() && ($session->get_username() == $member->get_username())) {
            echo '<p class="center">Don\'t be shy! Go ahead and <a href="write">write your first review</a>!';
        }
    } else { ?>
    <table class="reviewTable" width="100%" cellpadding="5"> 
        <thead class="reviewTableHead" align="center" valign="top">
            <tr class="reviewTableHead">
                <th></th>
            </tr>
        </thead>
        <tbody class="reviewTable">
            <?php
            foreach ($review_ids as $id) {
                $review = new Review($id);
                echo $review->to_html($session->is_authorized(),
                $member->get_username(), $in_school = false);
            } ?>
        </tbody>
    </table>
    <?php } ?>

    <br /><br /><br />


</div> <!-- Closes midBar -->
<?php $page->render_footer(); ?>
