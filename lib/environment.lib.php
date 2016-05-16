<?php

/** environment.lib.php -- LOCAL DEVELOPMENT VERSION

The Environment class provides static constant values for the environment the
page is in. Essentially, it is a place to store global variables that differ
depending on where the site is deployed.

*/

class Environment {
    static const DEBUG = true;

    static const URL = 'http://esr.colinstrong.ca';
    static const EMAIL = 'cstrong@sfu.ca';
    static const FLAG_EMAIL = 'cstrong@sfu.ca';

    static const FB_APP_ID = 125111874235106; 
    static const FB_APP_SECRET = '3d06cece1e70ae07e9d703b20ecad401'; 

    static const YAHOO_KEY = 'dj0yJmk9Nm9EMjRPQmQzOEE0JmQ9WVdrOVdUTkNiRXRXTjJjbWNHbzlORFkz
    TnpjMU1UWXkmcz1jb25zdW1lcnNlY3JldCZ4PTgx'; 
    static const YAHOO_SECRET = 'a3c71e682e11d6a3ed62be9dab7c44633f9476da'; 
    static const YAHOO_APP_ID = 'Y3BlKV7g'; 
}

?>
