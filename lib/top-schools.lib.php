<?php

require_once('stars.lib.php');
require_once('utils.php');

error_reporting(E_ALL);

class Top_Schools {

    private $number;
    private $desc;
    private $stars;

    public function __construct($number = 5) {
        $this->stars = new Stars();
        $this->stars->set_adjustable(false);
        $this->desc = 'DESC';
    }

    public function set_worst() {
        $this->desc = '';
    }

    public function show_schools() {
        $schools = $this->get_schools();
        require('settings.php');
        foreach ($schools as $school) {
            $this->stars->set_rating($school['averageStars']);
            $name = html($school['name']);
            $url = $this->create_url($school['id'], $name);
            $city = html(ucwords($school['city']));
            $country = html($school['country']);

            echo "<li><a href='$_URL/school/$url'>" . ucwords($name) . '</a></li>';
            echo "<span class='small'>$city, $country</span>";
            echo $this->stars->show_stars();
        }
    }

    private function create_url($id, $name) {
        return $id . '_' . hyphenate($name);
    }

    private function get_schools() {
        require('db-connect.php');
        try {
            $STH = $DBH->query("SELECT id, name, city, country, averageStars FROM schools ORDER BY averageStars DESC LIMIT 5");
            $DBH = null;
            return $STH->fetchAll();
        }
        catch(PDOException $exception) {
            echo $exception->getMessage();
        }
    }

}
