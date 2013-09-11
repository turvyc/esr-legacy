<?php

require_once('lib/settings.lib.php');

$url = "$_URL/hdl/facebook-login.hdl.php";

if(empty($code)) {
    $dialog_url = 'http://www.facebook.com/dialog/oauth?client_id=' 
    . $_FB_APP_ID . '&redirect_uri=' . urlencode($url).'&scope=email';

    echo("<script> top.location.href='$dialog_url'</script>");
}

$code = $_REQUEST['code'];
?>
