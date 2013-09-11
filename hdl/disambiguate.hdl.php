<?php

require('../lib/session.lib.php');
require('../lib/review.lib.php');
require('../lib/school.lib.php');
require('../lib/disambiguator.lib.php');
require_once('../lib/settings.lib.php');

$session = new Session();

// Make sure that we are only arriving from disambiguate.php
if (! isset($_SESSION['similarities'])) {
    if ($_DEBUG) {
        echo '$_SESSION["similarities"] not set.';
        exit(1);
    }
    header("location: $_URL");
    exit(0);
}

$review_data = $_SESSION['review_data'];
$school_data = $_SESSION['school_data'];

// Update the school data, depending on what was sent in the form. If the 
// values are different from the originals, that means the user chose one
// of the similar options found in the database, so update the school info
// accordingly. If the school name is the one of those found, the school
// already exists in the database and should be updated. Otherwise a new
// school will be inserted.
$update = false;
if (isset($_POST[School::CITY])) {
    if ($_POST[School::CITY] != $school_data[School::CITY]) {
        $school_data[School::CITY] = $_POST[School::CITY];
    }
}
if (isset($_POST[School::NAME])) {
    if ($_POST[School::NAME] != $school_data[School::NAME]) {
        $school_data[School::NAME] = $_POST[School::NAME];
        $update = true;
    }
}

// Update an existing school
if ($update) {
    require('db-connect.php');

    $STH = $DBH->prepare('SELECT id FROM schools WHERE name=? AND city=? AND 
    country=?');
    $STH->execute(array($school_data[School::NAME], $school_data[School::CITY], 
    $school_data[School::COUNTRY]));

    $id = $STH->fetch();
    $school = new School($id);
    $review_data[Review::SCHOOL] = $id;
}
// Create a new school
else {
    $school = School::create_new_school($school_data);
    $review_data[Review::SCHOOL] = $school->get_id();
}

// Write the review
Review::create_new_review($review_data);

// Do some cleanup
unset($_SESSION['review_data']);
unset($_SESSION['school_data']);
unset($_SESSION['similarities']);

header("location:$_URL/user/{$review_data['author']}");
exit();
}

?>
