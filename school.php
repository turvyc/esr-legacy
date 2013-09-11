<?php

require_once('lib/db-connect.php');
require_once('lib/review.lib.php');
require_once('lib/stars.lib.php');
require_once('lib/session.lib.php');
require_once('lib/school.lib.php');
require_once('lib/page.lib.php');
require_once('lib/exceptions.lib.php');
require_once('lib/settings.lib.php');
require_once('lib/utils.php');

$session = new Session();

// Cast the GET param from string to int. If the string isn't actually an
// integer, it will be evaluated at 0, so catch those cases.
if (!isset($_GET['id']) || ! (int)$_GET['id']) {
    header("location:$_URL/404");
    exit;
}

$id = (int)$_GET['id'];

try {
    $school = new School($id);
    $name = $school->get_name();
    $type = $school->get_type();
    $city = $school->get_city();
    $country = $school->get_country();
    $reviews = $school->get_number_of_reviews();
    $rating = $school->get_average_rating();
}
catch(PDOException $e) {
    //Log this, one day
    if ($_DEBUG) {
        echo $e;
    }
}
catch(SchoolException $e) {
    // Log this, one day
    if ($_DEBUG) {
        echo $e;
    }
    else {
        header("location:$_URL/404");
    }
}

$page = new Page($name);
$page->render_header();

?>

<div id="leftBar">
    <div id="averageRating">
        <h3 style="text-align:center">Average Rating</h3>
        <?php
        echo "<h1>$rating</h1>";
        $stars = new Stars($name, $rating, true, false);
        echo $stars->to_html();
        echo '<p>'.$reviews.' Review';
        if ($reviews != 1) { echo 's</p>'; } else { echo '</p>'; }
        ?>
        <a href="write?id=<?php echo $school->get_id(); ?>">Write your own review for this school</a>
        <br />
    </div>
    <hr />
    <h3>Browse</h3><br />
    <ul>
        <li><a class="plain" href="search?country=<?php echo $country; ?>&amp;
            type=<?php echo strtolower($type); ?>"><?php echo ucwords($type).
            's in '.ucwords($country);?></a></li>
        <li><br /></li>
        <li><a class="plain" href="search?city=<?php echo $city; ?>&amp;type=
            <?php echo strtolower($type); ?>"><?php echo ucwords($type).'s in '.
            ucwords($city);?></a></li>
        <li><br /><br /></li>
        <li><a class="plain" href="search?country=<?php echo $country; 
            ?>">Schools in <?php echo ucwords($country);?></a></li>
        <li><br /></li>
        <li><a class="plain" href="search?city=<?php echo $city; 
            ?>">Schools in <?php echo ucwords($city);?></a></li>
    </ul>
</div> <!-- Closes leftBar -->

<div id="midBar">
    <h1><?php echo $name; ?></h1><br /> 
    <h4><?php echo $school->get_description_html(true); ?></h4>
    <table class="legend" width="100%">
        <tr class="legend">
            <td><img class="icon" alt="workload icon" src="img/workload.png" /> Workload</td>
            <td><img class="icon" alt="admin icon" src="img/admin.png" /> Administration</td>
            <td><img class="icon" alt="recomp icon" src="img/recomp.png" /> Recompensation</td>
            <td><img class="icon" alt="accom icon" src="img/accom.png" /> Accomodation</td>
        </tr>
    </table>
    <table class="reviewTable" width="100%" cellpadding="5">
        <thead class="reviewTableHead" align="center" valign="top">
            <tr class="reviewTableHead">
                <th></th>
            </tr>
        </thead>
        <tbody class="reviewTable">
            <?php
            $reviews = $school->get_reviews();
            foreach($reviews as $review) {
                echo $review->to_html($session->is_authorized(), $session->get_username());
            } ?>
        </tbody>
    </table>
</div> <!-- Closes midBar -->

<?php $page->render_footer(); ?>
