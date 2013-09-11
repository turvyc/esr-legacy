<?php

class Writer {

    private $author; // The author is always the logged in user.
    private $school; // If the school doesn't already exist, this is null.

    public function __construct($author) {
        $this->author = Member::construct_from_username($author);
        $this->school = null;
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
        $input = array('workload'=>$this->workload, 'admin'=>$this->admin, 
        'accom'=>$this->accom, 'recomp'=>$this->recomp, 'average'=>$this->average, 
        'pros'=>$this->pros, 'cons'=>$this->cons, 'id'=>$review_id); 

        $STH = $DBH->prepare('UPDATE reviews SET date = CURDATE(), 
        workloadStars = :workload, adminStars = :admin, accomStars = :accom, 
        recompStars = :recomp, averageStars = :average, pros = :pros, cons = :cons, 
        edited = 1 WHERE id = :id');
        $STH->execute($input);

        $DBH = null;

        $this->update_school();
    }

    public function set_exists($bool = true) {
        $this->exists = $bool;
    }

    public function write_review($review_data) {
        if ($this->exists) {
            $this->update_school();
        }
        else {
            $this->create_school();
        }

        Review::new_review($review_data);
        $this->author->increment_number_of_reviews();

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
