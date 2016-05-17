<?php

require_once('exceptions.lib.php');

class Member {

    // These constants must match the column names in the database.
    const ID = 'id';
    const STATUS = 'status';
    const ACTIVATION_KEY = 'activationKey';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const EMAIL = 'email';
    const DATE_JOINED = 'joined';
    const NUMBER_OF_REVIEWS = 'reviews';
    const SALT = "%>n*N>DFl@FDuF*Zc.:mM@0M}dZ)3lX|V@ZY},ZMD_?d,~Nc:.Dh6[TY>)NZ&]N";

    const ACTIVATED = 'a';
    const PENDING = 'p';

    private $id;
    private $status;
    private $activation_key;
    private $username;
    private $password;
    private $email;
    private $date_joined;
    private $number_of_reviews;

    // This class should never be directly constructed. Instead, use one of the
    // construct_from_X methods, where X is the key used to find the user. The 
    // static constructor helper methods will return a new object populated with
    // the database row retrieved using the key.
    public function __construct($row) {
        $row = $row[0];
        $this->id = $row[Member::ID];
        $this->status = $row[Member::STATUS];
        $this->activation_key = $row[Member::ACTIVATION_KEY];
        $this->username = $row[Member::USERNAME];
        $this->password = $row[Member::PASSWORD];
        $this->email = $row[Member::EMAIL];
        $this->date_joined = $row[Member::DATE_JOINED];
        $this->number_of_reviews = $row[Member::NUMBER_OF_REVIEWS];
    }

    public static function construct_from_email($email) {
        require('db-connect.php');
        $STH = $DBH->prepare("SELECT * FROM members WHERE email=?");
        $STH->execute(array($email));
        $row = $STH->fetchAll();

        if (! $row) {
            throw new MemberException(MemberException::CONSTRUCTOR_SEED_NOT_FOUND);
        }

        return new Member($row);
    }

    public static function construct_from_key($key) {
        require('db-connect.php');
        $STH = $DBH->prepare("SELECT * FROM members WHERE activationKey=? AND status=?");
        $STH->execute(array($key, Member::PENDING));
        $row = $STH->fetchAll();

        if (! $row) {
            throw new MemberException(MemberException::CONSTRUCTOR_SEED_NOT_FOUND);
        }

        return new Member($row);
    }
    
    public static function construct_from_username($username) {
        require('db-connect.php');
        $STH = $DBH->prepare("SELECT * FROM members WHERE username=?");
        $STH->execute(array($username));
        $row = $STH->fetchAll();

        if (! $row) {
            throw new MemberException(MemberException::CONSTRUCTOR_SEED_NOT_FOUND);
        }

        return new Member($row);
    }

    public static function create_new_member($username, $password, $email) {
        $key = md5(mt_rand(10, 20) . mt_rand(10, 20)) . rand();
        $hash = Member::generate_hash($password);

        require('db-connect.php');
        $format = "INSERT INTO members (%s, %s, %s, %s, %s, %s) VALUES 
        (:username, :hash, :email, :key, :status, CURDATE())";
        $query = sprintf($format, Member::USERNAME, Member::PASSWORD, 
        Member::EMAIL, Member::ACTIVATION_KEY, Member::STATUS, Member::DATE_JOINED);
        $STH = $DBH->prepare($query);
        $STH->execute(array('username'=>$username, 'hash'=>$hash, 
        'email'=>$email, 'key'=>$key, 'status'=>Member::PENDING));
        $DBH = null;

        return Member::construct_from_username($username);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_possessive_username($current_user) {
        if ($current_user == $this->username) {
            return 'My';
        }

        return "$this->username's";
    }

    public function set_password($password) {
        $this->update(Member::PASSWORD, $password);
        $this->password = $password;
    }

    public function get_password() {
        return $this->password;
    }

    public function set_email($email) {
        $this->update(Member::EMAIL, $email);
        $this->email = $email;
    }

    public function get_email() {
        return $this->email;
    }

    public function get_date_joined() {
        return $this->date_joined;
    }

    public function increment_reviews() {
        $this->number_of_reviews += 1;
        $this->update(Member::NUMBER_OF_REVIEWS, $this->number_of_reviews);
    }

    public function get_number_of_reviews() {
        return $this->number_of_reviews;
    }

    public function set_activation_key($key) {
        $this->update(Member::ACTIVATION_KEY, $key);
        $this->activation_key = $key;
    }

    public function get_activation_key() {
        return $this->activation_key;
    }

    public function set_status($status) {
        $this->update(Member::STATUS, $status);
        $this->status = $status;
    }

    public function get_status() {
        return $this->status;
    }

    // Returns an array of the ids of the reviews this member has written.
    public function get_review_ids() {
        require('db-connect.php');
        $STH = $DBH->prepare('SELECT id FROM reviews WHERE author=? ORDER BY date DESC');
        $STH->execute(array($this->username));
        $review_ids = $STH->fetchAll();
        $DBH = null;

        $ids = array();
        foreach ($review_ids as $id) {
            $ids[] = (int)$id[Member::ID];
        }

        return $ids;
    }

    // Updates the user by setting the database $field to $value.
    private function update($field, $value) {
        // Ensure a valid field has been passed
        if ( ! (
        $field == Member::ACTIVATION_KEY ||
        $field == Member::STATUS ||
        $field == Member::USERNAME ||
        $field == Member::PASSWORD ||
        $field == Member::EMAIL ||
        $field == Member::DATE_JOINED ||
        $field == Member::NUMBER_OF_REVIEWS)) {
            throw new MemberException(MemberException::INVALID_FIELD);
        }

        try {
            require('db-connect.php');
            $query = 'UPDATE members SET ' . $field . '=? WHERE id=' . $this->id;
            $STH = $DBH->prepare($query);
            $STH->execute(array($value));
            $DBH = null;
        }
        catch(PDOException $e) {
            // Log this, one day
            throw new MemberException('Database error! The update probably 
            wasn\'t successful.');
        }
    }

    public function verify_password($password_to_be_checked) {
        if (Member::generate_hash($password_to_be_checked) != $this->password) {
            throw new MemberException(MemberException::INCORRECT_PASSWORD);
        }
    }

    public static function generate_hash($password) {
        return sha1(Member::SALT . $password);
    }
}
