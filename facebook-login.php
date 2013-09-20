<?php
require_once('lib/settings.lib.php');
require_once('lib/session.lib.php');
$session = new Session();
$session->set_info('Facebook login is disabled on the Legacy Site. Sorry!');
header("location: $_URL/info");

$url = "$_URL/hdl/facebook-login.hdl.php";

if(empty($code)) {
    $dialog_url = 'http://www.facebook.com/dialog/oauth?client_id=' 
    . $_FB_APP_ID . '&redirect_uri=' . urlencode($url).'&scope=email';

    echo("<script> top.location.href='$dialog_url'</script>");
}

$code = $_REQUEST['code'];
?>
