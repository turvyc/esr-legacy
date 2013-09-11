<?php

/** yahoo-login.hdl.php

Contains logic for adding a new user using their Yahoo account.

*/

require_once('../lib/settings.lib.php');
require_once('../lib/oauth.lib.php');
require_once('../lib/member.lib.php');
require_once('../lib/session.lib.php');

$request_token = $_GET['oauth_token'];
$nonce = substr(sha1(time() . rand()), 0, 8) // A random string

$url = "https://api.login.yahoo.com/oauth/v2/get_token
?oauth_consumer_key=$_YAHOO_KEY
&oauth_signature=$_YAHOO_SECRET%26{$_SESSION['token_secret']}
&oauth_signature_method=plaintext
&oauth_nonce=$nonce
&oauth_timestamp=" . time() . "
&oauth_version=1.0
&oauth_verifier={$_GET['oauth_verifier']}
&oauth_token={$_GET['oauth_token']}";

// Exchange the Request Token for an Access Token
$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
$token_string = urldecode(curl_exec($curl));
curl_close($curl);

// Convert $token_string into a $param => $value associative array
$access_array = array();
$pairs = explode('&', $token_string);
foreach ($pairs as $pair) {
    list($param, $value) = explode('=', $pair, 2);
    $access_array[$param] = $value;
}

// Prepare the parameters for the API request
$params = array();
$url = "http://social.yahooapis.com/v1/user/{$access_array['xoauth_yahoo_guid']}/profile";
$params['format'] = 'json';
$params['oauth_version'] = '1.0';
$params['oauth_nonce'] = mt_rand();
$params['oauth_timestamp'] = time();
$params['oauth_consumer_key'] = $_YAHOO_KEY;
$params['oauth_token'] = $access_array['oauth_token'];
$params['oauth_signature_method'] = 'HMAC-SHA1';
$params['oauth_signature'] = oauth_compute_hmac_sig('GET', $url, $params, 
$_YAHOO_SECRET, $access_array['oauth_token_secret']);

// Pass OAuth credentials in a separate header
$query_parameter_string = oauth_http_build_query($params, true);
$header = build_oauth_header($params, "yahooapis.com");
$headers[] = $header;

$request_url = $url . ($query_parameter_string ?  ('?' . $query_parameter_string) : '' );

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $request_url);
curl_setopt($curl, CURLOPT_PORT, 80);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$result = json_decode(curl_exec($curl), true);

// Get the username and email
$username = $result['profile']['nickname'];
$emails = $result['profile']['emails'];
foreach ($emails as $email) {
    if ($email['primary']) {
        $email = $email['handle'];
        break;
    }
}

// Check if the user already exists in the database by trying to create a 
// Member object using the retrieved username. If a row is returned, just
// log the user in and carry on. If not, add a new user to the database, 
// log them in, and carry on.
try {
    $member = Member::construct_from_username($username);
}
catch(MemberException $e) {
    // Create a bogus password -- the user will use their Yahoo password,
    // but something is still needed in the database.
    $hash = sha1(rand() . time() . Member::SALT);
    $member = Member::create_new_member($username, $hash, $email);
    $member->set_status(Member::ACTIVATED);
    $member->set_activation_key("YAHOO");
}

// Now log the user in and send them to their profile page
$session = new Session();
$session->login($member->get_username());

header("location:$_URL/user/$member->get_username()");
exit(0);

?>
