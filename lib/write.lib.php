<?php

error_reporting(E_ALL);

require_once('exception.lib.php');

class WriteException extends CustomException {}

class Writer {

    private $author;         // The author of the review
    private $school_id;      // The ID of a school in the 'schools' table
    private $name;           // The name of the school
    private $type;
    private $city;
    private $country;
    private $reviews;        // How many reviews have been written for this school
    private $pros;
    private $cons;
    private $workload;
    private $admin;
    private $accom;
    private $recomp;
    private $average;
    private $name_sound;
    private $city_sound;
    private $exists = false; // Boolean. Does the school already exist in the DB?
    private $edited = false;

    public function load_data($data) {
        $this->author = $data['author'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->city = $data['city'];
        $this->country = $data['country'];
        $this->pros = $data['pros'];
        $this->cons = $data['cons'];
        $this->workload = $data['workStars'];
        $this->admin = $data['adminStars'];
        $this->accom = $data['accomStars'];
        $this->recomp = $data['recompStars'];
        $this->name_sound = $data['nameSound'];
        $this->city_sound = $data['citySound'];

        if ($this->exists) {
            $this->school_id = $data['schoolID'];
            $this->school_average = $data['school_average'];
            $this->reviews = $data['school_reviews'];
        }

        $this->set_average();
    }

    public function update_review($data) {
        $review_id = $data['id'];
        $this->school_id = $data['school_id'];
        $this->workload = $data['workStars'];
        $this->admin = $data['adminStars'];
        $this->accom = $data['accomStars'];
        $this->recomp = $data['recompStars'];
        $this->pros = $data['pros'];
        $this->cons = $data['cons'];

        $this->set_average();
        $this->edited = true;

        require('db-connect.php');

        /* Update the review */
        $input = array('workload'=>$this->workload, 'admin'=>$this->admin, 'accom'=>$this->accom, 'recomp'=>$this->recomp, 'average'=>$this->average, 'pros'=>$this->pros, 'cons'=>$this->cons, 'id'=>$review_id); 
        $STH = $DBH->prepare('UPDATE reviews SET date = CURDATE(), workloadStars = :workload, adminStars = :admin, accomStars = :accom, recompStars = :recomp, averageStars = :average, pros = :pros, cons = :cons, edited = 1 WHERE id = :id');
        $STH->execute($input);

        $DBH = null;

        $this->update_school();
    }

    public function set_exists($bool = true) {
        $this->exists = $bool;
    }

    public function write_review() {

        if ($this->exists) {
            $this->update_school();
        }
        else {
            $this->create_school();
        }

        require('db-connect.php');

        $STH = $DBH->prepare("INSERT INTO reviews (school, name, author, date, workloadStars, adminStars, accomStars, recompStars, averageStars, pros, cons, edited) VALUES (:schoolID, :name, :author, CURDATE(), :workStars, :adminStars, :accomStars, :recompStars, :averageStars, :pros, :cons, :edited)");
        $data = array('schoolID'=>$this->school_id, 'name'=>$this->name, 'author'=>$this->author, 'workStars'=>$this->workload, 'adminStars'=>$this->admin, 'accomStars'=>$this->accom, 'recompStars'=>$this->recomp, 'averageStars'=>$this->average, 'pros'=>$this->pros, 'cons'=>$this->cons, 'edited'=>$this->edited);
        $STH->execute($data);

        # Increment reviews in the "members" table
        $STH = $DBH->prepare("UPDATE members SET reviews=reviews+1 WHERE username=?");
        $STH->execute(array($this->author));

        $DBH = null;
    }

    private function set_average() {
        $base = $this->workload + $this->admin + $this->recomp;
        $this->average = ( is_null($this->accom) ) ? $base / 3 : ($base + $this->accom) / 4;
    }

    /* Add a new school to the database */
    private function create_school() {

        require('db-connect.php');

        $this->set_average();

        $STH = $DBH->prepare("INSERT INTO schools (name, nameSound, country, type, city, citySound, reviews, averageStars) VALUES (:name, :nameSound, :country, :type, :city, :citySound, 1, :averageStars)");
        $data = array('name'=>$this->name, 'nameSound'=>$this->name_sound, 'country'=>$this->country, 'type'=>$this->type, 'city'=>$this->city, 'citySound'=>$this->city_sound, 'averageStars'=>$this->average);

        $STH->execute($data);

        /* This will select the last-added school ID */
        $STH = $DBH->query('SELECT id FROM schools ORDER BY id DESC LIMIT 1');
        $row = $STH->fetch();

        $this->school_id = $row['id'];

        $DBH = null;
    }

    /* If the review is for a school that already exists, we
    must update its averageStars and review number. */
    private function update_school() {

        require('db-connect.php');

        /* I admit this isn't the most elegant way to do things, but 
        there are a lot of differences between an edited and non-edited
        review. Maybe one day I'll come back and make this beautiful,
        but for now, it works, so fuck it. */
        if ($this->edited === false) {
            $this->reviews++;
            $data['reviews'] = $this->reviews;
            $review_query = 'reviews=:reviews,';
            $total = $this->average;
            $number = 1;
        }

        else {
            $data = array();
            $review_query = '';
            $total = $number = 0;
        }

        $STH = $DBH->prepare('SELECT averageStars FROM reviews WHERE school = ?');
        $STH->execute(array($this->school_id));

        while ($row = $STH->fetch()) {
            $total += $row['averageStars'];
            $number++;
        }

        $this->school_average = $total / $number;

        $data['average'] = $this->school_average;
        $data['id'] = $this->school_id;

        $STH = $DBH->prepare("UPDATE schools SET $review_query averageStars=:average WHERE id=:id");
        $STH->execute($data);

        $DBH = null;
    }
}

?>
