<?php

require('../lib/db-connect.php');
require_once('../lib/validator.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/session.lib.php');
require_once('../lib/stars.lib.php');
require_once('../lib/review.lib.php');
require_once('../lib/school.lib.php');
require_once('../lib/settings.lib.php');
require_once('../lib/utils.php');

$session = new Session();
$validator = new Validator();
$author = Member::construct_from_username($session->get_username());

// Ensure all the fields have been completed
try {
    if (! isset($_POST['noAccom'])) {
        $validator->verify_completed(Stars::ACCOM_VALUE);
    }
    $validator->verify_completed('country', 'city', 'name', 'type', 'pros', 
    'cons', Stars::WORK_VALUE, Stars::ADMIN_VALUE, Stars::RECOMP_VALUE);
}

catch (ValidationException $e) {
    if ($_DEBUG) {
        echo $e;
        exit(1);
    }
    $session->set_error('You didn\'t fill out all the fields! 
    Remember, all fields are mandatory.');
    // header("location:$_URL/write");
    exit(0);
}

// Load up an array with data of the school. This data will be used to 
// determine whether the school already exists.
$school_data = array();
$school_data[School::NAME] = $_POST[School::NAME];
$school_data[School::TYPE] = $_POST[School::TYPE];
$school_data[School::CITY] = $_POST[School::CITY];
$school_data[School::COUNTRY] = $_POST[School::COUNTRY];

// Load up an array with the data for the new review
$review_data = array();
$review_data[Review::AUTHOR] = $author->get_username();
$review_data[Review::WORK_RATING] = $_POST[Stars::WORK_VALUE];
$review_data[Review::ADMIN_RATING] = $_POST[Stars::ADMIN_VALUE];
$review_data[Review::ACCOM_RATING] = (isset($_POST[Review::NO_ACCOM])) ? 
null : $_POST[Stars::ACCOM_VALUE];
$review_data[Review::RECOMP_RATING] = $_POST[Stars::RECOMP_VALUE];
$review_data[Review::PROS] = $_POST[Review::PROS];
$review_data[Review::CONS] = $_POST[Review::CONS];

// Check if this exact school already exists in the database.
$existing_school = School::get_existing_school($school_data[School::NAME],
$school_data[School::TYPE], $school_data[School::CITY], $school_data[School::COUNTRY]);
if ($existing_school != null) {
    // Update the school, and write the review to the database
    $review_data[Review::SCHOOL] = $existing_school->get_id();
    $new_review = Review::create_new_review($review_data);
    $existing_school->add_review($new_review);
    $author->increment_reviews();
    // header("location:$_URL/user/{$input['author']}");
    exit(0);
}

// Try to find any similar schools for disambiguation
$similarities = School::get_similar_schools($school_data[School::NAME], 
$school_data[School::CITY], $school_data[School::COUNTRY]);

// If there are similar names or cities, save them and the school/review
// information as a SESSION variable, and go to disambiguation.
if (! (empty($similarities['names']) && empty($similarities['cities']))) {
    $_SESSION['similarities'] = $similarities;
    $_SESSION['review_data'] = $review_data;
    $_SESSION['school_data'] = $school_data;
    // header('location: ../disambiguate');
    exit(0);
}

// The school is unique; write it to the database, along with its first review.
$school_data[School::AVERAGE_RATING] = Review::calculate_average($review_data[Review::WORK_RATING], 
$review_data[Review::ADMIN_RATING], $review_data[Review::ACCOM_RATING], $review_data[Review::RECOMP_RATING]);

$new_school = School::create_new_school($school_data);
$review_data[Review::SCHOOL] = $new_school->get_id();
Review::create_new_review($review_data);
$author->increment_reviews();

// header("location:$_URL/user/{$input['author']}");

?>
