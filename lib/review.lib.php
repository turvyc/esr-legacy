<?php

require_once('stars.lib.php');
require_once('school.lib.php');

class Review {

    // These constants much match the respective field names in the reviews
    // table of the database.
    const ID = 'id';
    const AUTHOR = 'author';
    const SCHOOL = 'school';
    const DATE = 'date';
    const WORK_RATING = 'workloadStars';
    const ADMIN_RATING = 'adminStars';
    const RECOMP_RATING = 'recompStars';
    const ACCOM_RATING = 'accomStars';
    const AVERAGE_RATING = 'averageStars';
    const PROS = 'pros';
    const CONS = 'cons';
    const EDITED = 'edited';
    const FLAG = 'flag';
    const NO_ACCOM = 'noAccom';
    
    private $id;
    private $author;
    private $school;
    private $date;
    private $work_rating;
    private $admin_rating;
    private $recomp_rating;
    private $accom_rating;
    private $average_rating;
    private $pros;
    private $cons;
    private $edited;
    private $flag;

    /* Boolean. Is the review in the user's profile or
    in the school's page? Default to school. */
    private $in_school = true;

    public function __construct($id) {

        /*
        $this->id = $array[Review::ID];
        $this->author = $array[Review::AUTHOR];
        $this->school = $array[Review::SCHOOL];
        $this->date = $array[Review::DATE];
        $this->work_rating = $array[Review::WORK_RATING];
        $this->admin_rating = $array[Review::ADMIN_RATING];
        $this->recomp_rating = $array[Review::RECOMP_RATING];
        $this->accom_rating = $array[Review::ACCOM_RATING];
        $this->average_rating = $array[Review::AVERAGE_RATING];
        $this->pros = $array[Review::PROS];
        $this->cons = $array[Review::CONS];
        $this->edited = $array[Review::EDITED];
        $this->flag = 0;
        */

        if (! is_int($id)) {
            throw new ReviewException(ReviewException::INVALID_ID);
        }

        require('db-connect.php');
        $STH = $DBH->prepare("SELECT * FROM reviews WHERE id = ?");
        $STH->execute(array($id));

        if (! $review = $STH->fetch()) {
            throw new SchoolException(SchoolException::ID_NOT_FOUND);
        }

        $this->id = $id;
        $this->author = $review[Review::AUTHOR];
        $this->school = new School((int)$review[Review::SCHOOL]);
        $this->date = $review[Review::DATE];
        $this->work_rating = $review[Review::WORK_RATING];
        $this->admin_rating = $review[Review::ADMIN_RATING];
        $this->recomp_rating = $review[Review::RECOMP_RATING];
        $this->accom_rating = $review[Review::ACCOM_RATING];
        $this->average_rating = $review[Review::AVERAGE_RATING];
        $this->pros = $review[Review::PROS];
        $this->cons = $review[Review::CONS];
        $this->edited = $review[Review::EDITED];
        $this->flag = 0;

    }

    public static function calculate_average($work, $admin, $accom, $recomp) {
        $base = $work + $admin + $recomp;
        $average = ($accom === null) ? $base / 3 : ($base + $accom) / 4;
        return $average;
    }

    // Writes a new review to the database using data in an associative array, 
    // then returns said review as a Review object.
    public static function create_new_review($data) {
        require('db-connect.php');

        $data[Review::AVERAGE_RATING] = Review::calculate_average($data[Review::WORK_RATING], 
        $data[Review::ADMIN_RATING], $data[Review::ACCOM_RATING], $data[Review::RECOMP_RATING]);

        $format = "INSERT INTO reviews (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        VALUES (:%s, :%s, :%s, :%s, :%s, :%s, :%s, :%s, :%s, CURDATE(), 0, 0)";

        $query = sprintf($format, Review::SCHOOL, Review::AUTHOR, Review::WORK_RATING, 
        Review::ADMIN_RATING, Review::ACCOM_RATING, Review::RECOMP_RATING, Review::AVERAGE_RATING, Review::PROS, 
        Review::CONS, Review::DATE, Review::FLAG, Review::EDITED, 
        /* Begin array keys */ Review::SCHOOL, Review::AUTHOR, Review::WORK_RATING, 
        Review::ADMIN_RATING, Review::ACCOM_RATING, Review::RECOMP_RATING, Review::AVERAGE_RATING, Review::PROS, Review::CONS);

        $STH = $DBH->prepare($query);
        $STH->execute($data);
        $id = (int)$DBH->lastInsertId();
        $DBH = null;

        return new Review($id);
    }
        
    public function get_id() {
        return $this->id;
    }

    public function get_author() {
        return $this->author;
    }

    public function get_school() {
        return $this->school;
    }

    public function get_date() {
        return $this->date;
    }

    public function get_work_rating() {
        return $this->work_rating;
    }

    public function get_admin_rating() {
        return $this->admin_rating;
    }

    public function get_recomp_rating() {
        return $this->recomp_rating;
    }

    public function get_accom_rating() {
        return $this->accom_rating;
    }

    public function get_average_rating() {
        return $this->average_rating;
    }

    public function get_pros() {
        return $this->pros;
    }

    public function get_cons() {
        return $this->cons;
    }

    public function get_edited() {
        return $this->edited;
    }

    public function get_flag() {
        return $this->flag;
    }

    public function flag() {
        $hash = sha1($this->id . rand());
        $this->update(Review::FLAG, $hash);
    }

    public function update($field, $value) {
        // Ensure a valid field has been passed
        if ( ! (
        $field == Review::AUTHOR ||
        $field == Review::SCHOOL ||
        $field == Review::DATE ||
        $field == Review::WORK_RATING ||
        $field == Review::ADMIN_RATING ||
        $field == Review::RECOMP_RATING ||
        $field == Review::ACCOM_RATING ||
        $field == Review::AVERAGE_RATING ||
        $field == Review::PROS ||
        $field == Review::CONS ||
        $field == Review::EDITED ||
        $field == Review::FLAG)) {
            throw new ReviewException(ReviewException::INVALID_FIELD);
        }

        try {
            require('db-connect.php');
            $query = 'UPDATE reviews SET ' . $field . '=? WHERE id=' . $this->id;
            $STH = $DBH->prepare($query);
            $STH->execute(array($value));
            $DBH = null;
        }
        catch(PDOException $e) {
            // Log this, one day
            throw new ReviewException('Database error! The update probably 
            wasn\'t successful.');
        }
    }

    public function to_html($authorized, $username, $in_school = true) {
        $average_rating = new Stars($this->school->get_name(), $this->average_rating, true, false);
        $school_info = ($in_school) ? '' : $this->school->get_description_html();
        $pros_and_cons = $this->get_pros_and_cons();
        $edit_or_flag = ($authorized && $username == $this->author && ! $this->in_school) ? 
        "<a href='edit/$this->id'>edit</a>" : 
        "<a href='flag/$this->id' title='Flag this review'><img src='img/red-flag.png' /></a>";

        $review = "
        <a name='$this->id'></a>
        <tr class='reviewTable'>
            <td class='reviewTable'><h4>Rating</h4><h1>$this->average_rating</h1>{$average_rating->to_html()}
                <table class='ratings'>
                    <tr>
                        <td class='ratings'>
                            <img class='icon' src='img/workload.png' title='Workload' alt='Workload' />$this->work_rating
                        </td>
                        <td class='ratings'>
                            <img class='icon' src='img/admin.png' title='Administration' alt='Administration' />$this->admin_rating
                        </td>
                    </tr>
                    <tr>
                        <td class='ratings'>
                            <img class='icon' src='img/recomp.png' title='Recompensation' alt='Recompensation' />$this->recomp_rating
                        </td>
                        <td class='ratings'>
                            <img class='icon' src='img/accom.png' title='Accomodations' alt='Accomodations' />$this->accom_rating
                        </td>
                    </tr>
                </table>
            </td>
            <td class='reviewTable'>
                <h4>$school_info</h4>
                $pros_and_cons
            </td>
            <td class='reviewTable'>
                $edit_or_flag
            </td>
        </tr>";
        return $review;
    }

    private function get_pros_and_cons() {
        $written_by = $this->get_written_by();
        // HTML displaying the pros and cons
        $pros_and_cons = "
                    <li class='review'><h4>The Good: </h4>$this->pros</li><br />
                    <li class='review'><h4>The Bad: </h4>$this->cons</li>
                    <li class='review'><br />$written_by</li>";

        // HTML to show if the review has been flagged as inappropriate
        $flagged = "
                    <i>This review has been flagged as inappropriate.</i>";


        // One of the two variables above will be sandwiched in the two below
        $top = "
                <ul class='review'>";

        $bottom = "
                </ul>";

        $middle = ($this->flag) ? $flagged : $pros_and_cons;

        return $top . $middle . $bottom;
    }

    private function get_written_by() {
        require('settings.lib.php');
        $written = ($this->edited) ? 'Edited' : 'Written';
        $written .= ($this->in_school) ? " by <a href='$_URL/user/$this->author'>$this->author</a>" : '';
        return "<span class='small'>$written on $this->date</span>";
    }
}
