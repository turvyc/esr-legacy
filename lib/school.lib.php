<?php

require_once('exceptions.lib.php');

class School {

    // These constants will be for the keys of an associative array containing
    // information about a school.
    const ID = 'id';
    const NAME = 'name';
    const TYPE = 'type';
    const CITY = 'city';
    const COUNTRY = 'country';
    const NUMBER_OF_REVIEWS = 'reviews';
    const AVERAGE_RATING = 'averageStars';

    private $id;
    private $name;
    private $type;
    private $city;
    private $country;
    private $number_of_reviews;
    private $average_rating;

    public function __construct($seed) {
        /*
        if (is_array($seed)) {
            if (! array_key_exists($seed[School::ID])) {
                throw new SchoolException(SchoolException::INVALID_SEED);
            }

            $this->id = $array[School::ID];
            $this->name = $array[School::NAME];
            $this->type = $array[School::TYPE];
            $this->city = $array[School::CITY];
            $this->country = $array[School::COUNTRY];
            $this->number_of_reviews = $array[School::NUMBER_OF_REVIEWS];
            $this->average_rating = $array[School::AVERAGE_RATING];

        }
        */

        if (! is_int($seed)) {
            throw new SchoolException(SchoolException::INVALID_SEED);
        }

        require('db-connect.php');
        $STH = $DBH->prepare("SELECT * FROM schools WHERE id=?");
        $STH->execute(array($seed));

        if (! $school = $STH->fetch()) {
            throw new SchoolException(SchoolException::ID_NOT_FOUND);
        }

        $this->id = $seed;
        $this->name = $school[School::NAME];
        $this->type = $school[School::TYPE];
        $this->city = $school[School::CITY];
        $this->country = $school[School::COUNTRY];
        $this->number_of_reviews = $school[School::NUMBER_OF_REVIEWS];
        $this->average_rating = $school[School::AVERAGE_RATING];

        $DBH = null;
    }

    // Writes a new school to the database, and returns a School object of
    // the school just created.
    public static function new_school($data) {
        require('db-connect.php');
        // $format = "INSERT INTO schools (%s, %s, %s, %s, %s, %s, %s, %) VALUES
        // (%s, %s, %s, %s, %s, %s, 
    }

    public function get_id() {
        return $this->id;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_type() {
        return $this->type;
    }

    public function get_city() {
        return $this->city;
    }

    public function get_country() {
        return $this->country;
    }

    public function get_number_of_reviews() {
        return $this->number_of_reviews;
    }

    public function get_average_rating() {
        return $this->average_rating;
    }

    public function get_url() {
        return $this->id . '_' . str_replace(' ', '-', $this->name);
    }

    // Returns an array of Review objects.
    public function get_reviews() {
        require('db-connect.php');
        $STH = $DBH->prepare("SELECT id FROM reviews WHERE school = ?");
        $STH->execute(array($this->id));

        while ($row = $STH->fetch()) {
            $review = new Review((int)$row['id']);
            $reviews[] = $review;
        }

        return $reviews;
    }

    // Returns a description of the school in the form "<name>, a <type> in
    // <city>, <country>". The variables are linked to an appropriate search
    // page. If $no_name is true, the school's name is not included.
    public function get_description_html($no_name = false, $a_class = 'plain') {
        if (! $no_name) {
            $name = ucwords($this->name);
            $name = "<a class='$a_class' href='school/{$this->get_url()}'>$name</a>, ";
        } else { $name = ''; }

        $type = "<a class='$a_class' title='Read more $this->type reviews' 
            href='search?type=$this->type'>$this->type</a>";
        $city = "<a class='$a_class' title='Read more reviews of schools in $this->city'
            href='search?city=$this->city'>$this->city</a>";
        $country = "<a class='$a_class' title='Read more reviews of schools in $this->country'
            href='search?country=$this->country'>$this->country</a>";

        $format = "%sa %s in %s, %s";
        return sprintf($format, $name, $type, $city, $country);
    }

    // Returns an array of objects of the top n schools.
    public static function get_top_schools($n) {
        require('db-connect.php');
        $query = "SELECT id FROM schools ORDER BY averageStars DESC LIMIT $n";
        $STH = $DBH->query($query);
        
        while ($row = $STH->fetch()) {
            $school = new School((int)$row['id']);
            $top_schools[] = $school;
        }

        return $top_schools;
    }

    private function calculate_average($new_rating) {
        require('db-connect.php');

        $sum = $new_rating; // The sum of the average rating of all reviews
        $reviews = 1;       // The number of reviews written for this school

        try {
            $STH = $DBH->prepare('SELECT averageStars FROM reviews WHERE school = ?');
            $STH->execute(array($this->id));
        }
        catch(PDOException $e) {
            // Log this, one day
        }

        while ($review = $STH->fetch()) {
            $sum += $review['averageStars'];
            $reviews += 1;
        }

        return $sum / $reviews;
    }
}
