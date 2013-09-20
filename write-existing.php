<?php

require_once('lib/settings.lib.php');

session_start();

$_SESSION['write_existing'] = true;

header("location:$_URL/write");

exit(0);

?>
