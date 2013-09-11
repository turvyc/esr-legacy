<?php

require_once('../lib/db-connect.php');
require_once('../lib/validator.lib.php');
require_once('../lib/write.lib.php');
require_once('../lib/settings.lib.php');
require_once('../utils.php');

session_start();

$validator = new Validator();
$writer = new Writer();

try {

    if ( ! isset($_POST['noAccom'])) {
        $validator->verify_completed('starForm2');
    }

    $validator->verify_completed('pros', 'cons', 'starForm0', 'starForm1', 'starForm3');
}

catch (ValidationException $exception) {

    $_SESSION['write_error'] = info('You didn\'t fill out all the fields! Remember, all fields are mandatory.');
    header("location:$_URL/write");
    exit(1);

}

$input['id'] = $_POST['review_id'];
$input['school_id'] = $_POST['school_id'];
$input['workStars'] = $_POST['starForm0'];
$input['adminStars'] = $_POST['starForm1'];
$input['accomStars'] = (isset($_POST['noAccom'])) ? null : $_POST['starForm2'];
$input['recompStars'] = $_POST['starForm3'];
$input['pros'] = $_POST['pros'];
$input['cons'] = $_POST['cons'];

$writer->update_review($input);

header("location:$_URL/user/{$_SESSION['username']}");

?>
