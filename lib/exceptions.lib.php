<?php

/** exceptions.lib.php

Provides a custom exception superclass for ESL School Rater (ESR), as well as
descriptive subclasses and error messages for use in specific situations.

**/


class ESRException extends Exception {

    public function __toString() {
        require('settings.lib.php');
        if (! $_DEBUG) {
            return $this->getMessage();
        }

        else {
            $format = "%s: Line %s. %s %s\n";
            return sprintf($format, $this->getFile(), $this->getLine(), 
            $this->getMessage(), $this->getTraceAsString());
        }
    }
}

class NewsException extends ESRException {}

class MemberException extends ESRException {

    const CONSTRUCTOR_SEED_NOT_FOUND = 'Constructor seed not found in database.';
    const INVALID_FIELD = 'Database field does not exists.';

}

class ReviewException extends ESRException {

    const INVALID_ID = 'Invalid ID -- must be integer';
    const ID_NOT_FOUND = 'Review not found in database.';
    const INVALID_FIELD = 'Database field does not exist.';

}

class CookieException extends ESRException {

   const COOKIE_NOT_FOUND = 'The cookie was not found in the database'; 
   const COOKIE_NOT_SET = 'Setting the cookie failed.';
}

class SessionException extends ESRException {

    const LOGIN_ERROR = 'Either your username doesn\'t exist or the password 
    isn\'t correct. In either case, give it another go!';
    const DATABASE_ERROR = 'There was an unfortunate error in the database. 
    You better <a href="contact">let us know</a>.';
    const NO_TITLE = 'The page title has not been set.';
    const NO_INFO = 'No information message has been set.';

}

class ValidationException extends ESRException {

    const EMPTY_FIELD = 'I don\'t think you filled in all the fields. Why don\'t you double-check and have another go?';
    const INVALID_EMAIL = 'The digital monkeys don\'t think you entered a valid e-mail. Have another try, alright?';
    const PASSWORDS_DONT_MATCH = 'I reckon the passwords don\'t match! Have another go.';
    const PASSWORD_TOO_SHORT = 'Your password is too short! It should be at least 6 characters long.';
    const USERNAME_LENGTH_ERROR = 'For sanity\'s sake, let\'s keep the username between 3 and 24 characters, shall we?';
    const INVALID_CHAR = 'Regrettably, your username can only contain numbers, letters, underscores, and dashes.';
    const USERNAME_TAKEN = 'Hate to say it, but somebody has already registered with that username.';
    const EMAIL_TAKEN = 'Hate to say it, but somebody has already registered with that e-mail address.';
    const INVALID_RECAPTCHA = 'Unfortunately, reCAPTCHA doesn\'t agree with you. Have another try.';

}

class SchoolException extends ESRException {

    const INVALID_SEED = 'Invalid argument type. Argument must be an integer.';
    const INVALID_FIELD = 'Invalid update field.';
    const ID_NOT_FOUND = 'School not found in database.';

}
