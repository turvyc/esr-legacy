<?php

// Cleans up user-submitted data for output onto the page. Some HTML allowed.
function html($string) {
    return nl2br(strip_tags(stripslashes($string), '<b><i><strong><em>'));
}


// Takes the MySQL-formatted date and returns a pretty one
function myDate($date) {
    date_default_timezone_set("America/Vancouver");
    $phpdate = strtotime($date);
    return date('F j, Y', $phpdate);
}


