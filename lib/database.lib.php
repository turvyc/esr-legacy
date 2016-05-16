<?php

/** database.lib.php

Provides a wrapper class for all database interactions.

*/

class Database {

    static const DATABASE_NAME = 'colinst1_esr_legacy';
    static const USERNAME = 'colinstr1_esr';
    static const PASSWORD = 'tomcat';
    static const HOSTNAME = 'localhost';

    public function __construct() {

        $format = "mysql:host=%s;dbname=%s,%s,%s)";
        $pdo_params = sprintf($format, Database::HOSTNAME, 
        Database::DATABASE_NAME, Database::USERNAME, Database::PASSWORD);

        try {
            $DBH = new PDO($pdo_params);
            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DBH->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            echo 'There has been an error connecting to the database: ' . $exception->getMessage();
            exit(1);
        }
    }
}

?>
