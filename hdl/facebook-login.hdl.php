<?php 

/** facebook-login.hdl.php

Contains logic for adding a new user using their Facebook account.

*/

require_once('../lib/settings.lib.php');
require('../lib/db-connect.php');
require('../lib/member.lib.php');
require('../lib/session.lib.php');

// The user must grant ESR permission to access their Facebook information. If
// the user does not grant permission, redirect to the manual login page.
if($_GET['error']){
    header("location:$_URL/login");
    exit(1);
}

$local_url = "$_URL/hdl/facebook-login.hdl.php";
$token_url = 'https://graph.facebook.com/oauth/access_token?client_id='
. $_FB_APP_ID . '&redirect_uri=' . urlencode($local_url) . '&client_secret='
. $_FB_APP_SECRET . '&code=' . $_GET['code'];

// Request an access token from Facebook
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$access_token = curl_exec($ch);
curl_close($ch);

// Request user data, which is returned as a JSON array
$graph_url = 'https://graph.facebook.com/me?' . $access_token;
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $graph_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$json_array = curl_exec($ch);
curl_close($ch);

//decode the json array to get user data
$user_info = json_decode($json_array);

// Check if the user already exists in the database by trying to create a 
// Member object using the retrieved username. If a row is returned, just
// log the user in and carry on. If not, add a new user to the database, 
// log them in, and carry on.
try {
    $member = Member::construct_from_username($user->username);
}
catch(MemberException $e) {
    // Create a bogus password -- the user will use their Facebook password,
    // but something is still needed in the database.
    $hash = sha1(rand() . time() . Member::SALT);
    $member = Member::create_new_member($user->username, $hash, $user->email);
    $member->set_status(Member::ACTIVATED);
    $member->set_activation_key("FACEBOOK");
}

// Now log the user in and send them to their profile page
$session = new Session();
$session->login($member->get_username());

header("location:$_URL/user/$member->get_username()");
exit(0);

?>
