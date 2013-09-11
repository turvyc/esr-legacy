<?php
# utils.php
# Useful functions!

/* Used to create a new random password */
function generate_password($length) {
    $characters = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $string = '';    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[rand(0, count($characters))];
    }
    return $string;
}

function info($message) {
    return "<h3 class='info'>$message</h3><br />";
}

function html($string) {
    # Cleans up user-submitted data for output onto the page. Some HTML allowed.
    return nl2br(strip_tags(stripslashes($string), '<b><i><strong><em>'));
}

function hyphenate($string) {
    return str_replace(' ', '-', $string);
}

function currentURL() {
    # Returns the current URL. Thanks to www.webcheatsheet.com
    $pageURL = 'http://';
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function myDate($date) {
    # Takes the MySQL-formatted date and returns a pretty one
    date_default_timezone_set("America/Vancouver");
    $phpdate = strtotime($date);
    return date('F j, Y', $phpdate);
}


