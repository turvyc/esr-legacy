<?php

/** settings.lib.php -- DEVELOPMENT VERSION

These are global variables that change depending on the environment the
page is in: development, staging, or production.


define("_DEBUG", true);

define("_URL", 'http://localhost/eslschoolrater.dev');
define("_EMAIL", 'turvyc@gmail.com');
define("_FLAG_EMAIL", 'turvyc@gmail.com');

define("_FB_APP_ID", 125111874235106);
define("_FB_APP_SECRET", '3d06cece1e70ae07e9d703b20ecad401');

define("_YAHOO_KEY", 'dj0yJmk9Nm9EMjRPQmQzOEE0JmQ9WVdrOVdUTkNiRXRXTjJjbWNHbzlORFkTnpjMU1UWXkmcz1jb25zdW1lcnNlY3JldCZ4PTgx');
define("_YAHOO_SECRET", 'a3c71e682e11d6a3ed62be9dab7c44633f9476da');
define("_YAHOO_APP_ID", 'Y3BlKV7g');

*/

$_URL = 'http://localhost/eslschoolrater.dev';
$_DOC_ROOT = 'eslschoolrater.dev';
$_EMAIL = 'turvyc@gmail.com';
$_FLAG_EMAIL = 'turvyc@gmail.com';

$_DEBUG = TRUE;

#-- Database Variables --#
$_DB_NAME = 'eslschoolrater';
$_DB_USER = 'colin';
$_DB_PASS = 'tomcat';
$_DB_HOST = 'localhost';

#-- Auth Variables --#

$_SALT = '%>n*N>DFl@FDuF*Zc.:mM@0M}dZ)3lX|V@ZY},ZMD_?d,~Nc:.Dh6[TY>)NZ&]N';

$_FB_APP_ID = 125111874235106; 
$_FB_APP_SECRET = '3d06cece1e70ae07e9d703b20ecad401'; 

$_YAHOO_KEY = 'dj0yJmk9Nm9EMjRPQmQzOEE0JmQ9WVdrOVdUTkNiRXRXTjJjbWNHbzlORFkzTnpjMU1UWXkmcz1jb25zdW1lcnNlY3JldCZ4PTgx'; 
$_YAHOO_SECRET = 'a3c71e682e11d6a3ed62be9dab7c44633f9476da'; 
$_YAHOO_APP_ID = 'Y3BlKV7g'; 
?>
