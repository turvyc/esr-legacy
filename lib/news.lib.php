<?php

/** news.lib.php

Handles inserting new news posts into the database, and rendering the posts
into HTML.

*/

require('utils.php');
require('settings.lib.php');

class News {

    // Simply inserts a new post into the database
    public function new_post($post) {
        require('db-connect.php');
        try {
            $STH = $DBH->prepare("INSERT INTO blog (date, post) VALUES (CURDATE(), ?)");
            $STH->execute(array($post));
            $DBH = null;
        }

        catch(PDOException $e) {
            if ($_DEBUG) {
                echo $e;
                exit(1);
            }
            throw NewsException("Database error when trying to add news post.");
        }
    }

    public function get_news($limit = 0) {
        require('db-connect.php');

        $query = 'SELECT date, post FROM blog ORDER BY date DESC';
        $query .= ($limit) ? " LIMIT $limit" : '';
        $html = '';

        try {
            $STH = $DBH->query($query);
            while ($row = $STH->fetch()) {
                $date = myDate($row['date']);
                $post = nl2br(stripslashes($row['post']));

                $html .= "<h4>$date</h4><br /><p style='padding-left:10px;
                text-align:justify;'>$post.</p><br />\n";
            }
            $DBH = null;
        }
        catch(PDOException $error) {
            if ($_DEBUG) {
                echo $e;
                exit(1);
            }
            throw NewsException("Database error when retrieving news posts.");
        }

        return $html;
    }

}
