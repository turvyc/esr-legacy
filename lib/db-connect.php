<?php
require('settings.lib.php');
try {
   $DBH = new PDO("mysql:host=$_DB_HOST;dbname=$_DB_NAME",$_DB_USER,$_DB_PASS);
    $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $DBH->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    echo 'There has been an error connecting to the database: ' . $exception->getMessage();
}
?>
