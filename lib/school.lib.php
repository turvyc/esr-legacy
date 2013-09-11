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
    const NAME_SOUND = 'nameSound';
    const CITY_SOUND = 'citySound';

    private $id;
    private $name;
    private $type;
    private $city;
    private $country;
    private $number_of_reviews;
    private $average_rating;
    private $name_sound;
    private $city_sound;

    public function __construct($seed) {
        if (is_int($seed)) {

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
            $this->name_sound = $school[School::NAME_SOUND];
            $this->city_sound = $school[School::CITY_SOUND];

            $DBH = null;
        }

        else {
            throw new SchoolException(SchoolException::INVALID_SEED);
        }
    }

    // Writes a new school to the database, and returns its School object.
    public static function create_new_school($data) {
        require('db-connect.php');
        $data[School::NAME_SOUND] = soundex($data[School::NAME]);
        $data[School::CITY_SOUND] = soundex($data[School::CITY]);

        $format = "INSERT INTO schools (%s, %s, %s, %s, %s, %s, %s, %s) VALUES 
        (:%s, :%s, :%s, :%s, :%s, :%s, %d, :%s)";
        $query = sprintf($format, School::NAME, School::NAME_SOUND, School::COUNTRY, 
        School::TYPE, School::CITY, School::CITY_SOUND, School::NUMBER_OF_REVIEWS, 
        School::AVERAGE_RATING, /* Begin array keys */ School::NAME, School::NAME_SOUND, 
        School::COUNTRY, School::TYPE, School::CITY, School::CITY_SOUND, 1, School::AVERAGE_RATING);

        $STH = $DBH->prepare($query);
        $STH->execute($data);

        $id = (int)$DBH->lastInsertId();
        $DBH = null;
        return new School($id);
    }

    // Adds a new review to a school by incrementing the number of reviews
    // written for it, as well as recalculating its average rating.
    public function add_review($new_review) {
        require('db-connect.php');

        // Increment the number of reviews
        $this->number_of_reviews += 1;
        $this->update(School::NUMBER_OF_REVIEWS, $this->number_of_reviews);

        // Re-calculate the school's average rating
        $sum = 0; // The sum of the average rating of all reviews

        $STH = $DBH->prepare('SELECT averageStars FROM reviews WHERE school = ?');
        $STH->execute(array($this->id));

        while ($review = $STH->fetch()) {
            $sum += $review['averageStars'];
        }

        $new_average = $sum / $this->number_of_reviews;

        $this->average_rating = $new_average;
        $this->update(School::AVERAGE_RATING, $new_average);

        $DBH = null;
    }

    // Removes a review from a school by decrementing the number of reviews
    // written for it, as well as recalculating its average rating. This is
    // required for edited reviews (the review will be removed then added again)
    // or for flagged reviews.
    public function remove_review($doomed_review) {
        require('db-connect.php');

        // Decrement the number of reviews
        $this->number_of_reviews -= 1;
        $this->update(School::NUMBER_OF_REVIEWS, $this->number_of_reviews);

        // Recalculate the school's average rating
        $sum = 0;
        $STH = $DBH->prepare('SELECT averageStars FROM reviews WHERE school = ?');
        $STH->execute(array($this->id));

        while ($review = $STH->fetch()) {
            $sum += $review['averageStars'];
        }

        $sum -= $doomed_review->get_average_rating();
        $new_average = $sum / $this->number_of_reviews;

        $this->average_rating = $new_average;
        $this->update(School::AVERAGE_RATING, $new_average);

        $DBH = null;
    }

    // Returns the school with the identical attributes as the parameters, or 
    // null if no such school is found.
    public static function get_existing_school($name, $type, $city, $country) {
        require('db-connect.php');
        $STH = $DBH->prepare("SELECT id FROM schools WHERE name=? AND type=? 
        AND city=? AND country=?");
        $STH->execute(array($name, $type, $city, $country));
        $DBH = null;

        if ($id = $STH->fetch()) {
            return new School((int)$id[School::ID]);
        }
        return null;
    }

    // Returns an associative array containing one or both of the keys 'names'
    // and 'cities', or neither. The keys point to unaccociative arrays containing
    // the list of similar names or cities, if applicable.
    public static function get_similar_schools($name, $city, $country) {
        require('db-connect.php');
        $similarities = array('names'=>array(), 'cities'=>array());

        $STH = $DBH->prepare('SELECT name FROM schools WHERE ((name LIKE ?) 
        OR (nameSound LIKE ?)) AND ((city LIKE ?) OR (citySound LIKE ?))
        AND (country = ?)');
        $STH->execute(array($name, soundex($name), $city, soundex($city), $country));
        while ($similar_name = $STH->fetch()) {
            $similarities['names'][] = $similar_name;
        }

        $STH = $DBH->prepare('SELECT city FROM schools WHERE ((city LIKE ?) 
        OR (citySound LIKE ?)) AND (country = ?)');
        $STH->execute(array($city, soundex($city), $country));
        while ($similar_city = $STH->fetch()) {
            $similarities['cities'][] = $similar_city;
        }

        $DBH = null;

        return $similarities;
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

    public function update($field, $value) {
        // Ensure a valid field has been passed
        if ( ! (
        $field == School::NAME ||
        $field == School::CITY ||
        $field == School::NAME_SOUND ||
        $field == School::CITY_SOUND ||
        $field == School::COUNTRY ||
        $field == School::NUMBER_OF_REVIEWS ||
        $field == School::AVERAGE_RATING ||
        $field == School::TYPE)) {
            throw new SchoolException(SchoolException::INVALID_FIELD);
        }

        try {
            require('db-connect.php');
            $query = 'UPDATE schools SET ' . $field . '=? WHERE id=' . $this->id;
            $STH = $DBH->prepare($query);
            $STH->execute(array($value));
            $DBH = null;
        }
        catch(PDOException $e) {
            // Log this, one day
            throw new SchoolException('Database error! The update probably 
            wasn\'t successful.');
        }
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
}
