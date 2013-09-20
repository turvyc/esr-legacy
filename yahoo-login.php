<?php

/** yahoo-login.php

Retrieves a token secret from Yahoo and stores it in a session variable. The
user is then forwarded to the Yahoo authentication page, and upon returning to
ESR they are sent to yahoo-login.hdl.php, where the token secret is retrieved.
Complicated, I know.

*/

require_once('lib/settings.lib.php');
require_once('lib/session.lib.php');
$session = new Session();
$session->set_info('Yahoo login is disabled on the Legacy Site. Sorry!');
header("location: $_URL/info");

// Prepare the URL to get the Request Token
$url = 'https://api.login.yahoo.com/oauth/v2/get_request_token'; 
$url .= "?oauth_consumer_key=$_YAHOO_KEY";
$url .= "&oauth_signature=$_YAHOO_SECRET" . '%26';
$url .= '&oauth_signature_method=plaintext';
$url .= '&oauth_nonce=' . substr(sha1(time() . rand()), 0, 8); // A random string
$url .= '&oauth_timestamp=' . time();
$url .= '&oauth_version=1.0';
$url .= '&oauth_callback=http://www.eslschoolrater.com/hdl/yahoo-login.hdl.php';

// Get the Request Token
$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

$request_token = urldecode(curl_exec($curl));
curl_close($curl);

// Translate the raw GET parameters into a nice associative array
$get_params = array();
$temp_array = explode('&', $request_token);
foreach ($temp_array as $key=>$value) {
    $array = explode('=', $value);
    $get_params[$array[0]] = $array[1];
}

// Put the Token Secret into a SESSION variable, because we need it later and 
// Yahoo doesn't forward it.
$session = new Session();
$_SESSION['token_secret'] = $get_params['oauth_token_secret'];
$redirect = $get_params['xoauth_request_auth_url'] . '=' . $get_params['oauth_token'];
header('location: ' . $redirect);

?>
