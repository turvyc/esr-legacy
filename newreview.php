<?php
# File: newreview
# Info: Logic related to submitting a new review
require_once('includes/db-connect.php');
require_once('utils.php');
session_start();

// Set variables and sanitize inputs
$author = $_SESSION['username'];
$country = (isset($_POST['country'])) ? $_POST['country'] : '';
$city = (isset($_POST['city'])) ? strtolower($_POST['city']) : '';
$name = (isset($_POST['name'])) ? $_POST['name'] : '';
$type = (isset($_POST['type'])) ? strtolower($_POST['type']) : '';
$accomProvided = (isset($_POST['noAccom'])) ? 0 : 1;
$pros = (isset($_POST['pros'])) ? $_POST['pros'] : '';
$cons = (isset($_POST['cons'])) ? $_POST['cons'] : '';

$workStars = (isset($_POST['starForm0'])) ? $_POST['starForm0'] : '';
$adminStars = (isset($_POST['starForm1'])) ? $_POST['starForm1'] : '';
$accomStars = ($accomProvided == 1 ? $_POST['starForm2'] : '');
$recompStars = (isset($_POST['starForm3'])) ? $_POST['starForm3'] : '';

# Calculate the soundex values for the city and name. This will help prevent duplicate entries.
$nameSound = soundex($name);
$citySound = soundex($city);

function badForm($error) {
    require_once('template-top.php');
    title('Review Error');
    echo '<h3 style="text-align:center;line-height:10em;">You have an error in your review: ' . $error . '</h3>';
    require_once('template-bottom.php');
}

function verify($name, $type, $country, $city, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars) {
    # Make sure required fields have been filled out

    if ($name == 'School name' || $name == '') { 
        badForm('Please fill in the name of your school.'); 
        return false;
    } elseif ($type == '') { 
        badForm('Please select your school\'s type.'); 
        return false; 
    } elseif ($country == '') { 
        badForm('Please select the country your school is in.'); 
        return false; 
    } elseif ($city == '' || $city == 'City or town') { 
        badForm('Please include the city your school is in.'); 
        return false; 
    } elseif ($accomProvided == 0) {
        if ($workStars == '' || $adminStars == '' || $recompStars == '') {
            badForm('Please give a star rating for all three categories.'); 
            return false; 
        }
    } elseif ($accomProvided == 1) {
        if ($workStars == '' || $adminStars == '' || $accomStars == '' || $recompStars == '') {
            badForm('Please give a star rating for all four categories.');
            return false; 
        }
    } elseif ($cons == '') { 
        badForm('Please describe the bad things about your school.'); 
        return false; 
    } elseif ($pros == '') { 
        badForm('Please describe the good things about your school.'); 
        return false; 
    }
    return true;
}

function checkExisting($author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars) {
    global $DBH;
    $verifyCity = $_POST['verifyCity'];
    $STH = $DBH->prepare("SELECT id, reviews, city, country FROM schools WHERE name LIKE :name AND country LIKE :country AND city LIKE :city AND type LIKE :type AND citySound LIKE :citySound");
    $data = array('name'=>$name, 'country'=>$country, 'city'=>$city, 'type'=>$type, 'citySound'=>'%');

    # First check for exact matches
    $STH->execute($data);
    if ($row = $STH->fetch()) {
        $schoolID = $row['id'];
        $reviews = $row['reviews'] + 1; # Increment the number of reviews written for this school
        return write(true, $schoolID, $reviews, $author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars);
    } 

    # Check for exact cities
    $data['name'] = '%';
    $data['type'] = '%';
    $STH->execute($data);
    if ($row = $STH->fetch()) {
        $schoolID = $row['id'];
        $reviews = $row['reviews'];
        return write(false, $schoolID, $reviews, $author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars);
    }

    # Now check for similar cities
    $data['city'] = '%';
    $data['citySound'] = $citySound;
    $STH->execute($data);
    if ($STH->fetch() && $verifyCity == 0) {
        $STH->execute($data);
        return disambiguate($STH, $author, $name, $type, $country, $city, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars);
    } 

    # Nothing similar, or they have verified it should be a new city. Create a new review
    return write(false, '', 1, $author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars);
}

function disambiguate($STH, $author, $name, $type, $country, $city, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars) {
    global $DBH;
    require_once('template-top.php');
    title('School Disambiguation'); ?>
    <div id="midBar">
        <h1>Disambiguation</h1><br />
        <p>The database monkeys have found some similar-sounding cities. Are you sure you didn't mean one of these?</p><br />
        <form method="post" action="newreview">
            <?php
            $cities = array();
            $i = 0;
            while ($row = $STH->fetch()) {
                $id = $row['id'];
                $possibleCity = $row['city'];
                $country = $row['country'];
                if (!in_array($possibleCity, $cities)) {
                    echo '<input type="radio" name="city" value="'.$possibleCity.'"> '.ucwords($possibleCity).', '.ucwords($country).'<br />';
                }
                $cities[] = $possibleCity;
                $i++;
            } 
            ?>
            <input type="radio" name="city" value="<?php echo $city; ?>"> Nope, it's not one of these. <br />
            <input type="hidden" name="verifyCity" id="verifyCity" value="1" />
            <input type="hidden" name="author" id="author" value="<?php echo $author; ?>" />
            <input type="hidden" name="name" id="name" value="<?php echo $name ?>" />
            <input type="hidden" name="type" id="type" value="<?php echo $type; ?>" />
            <input type="hidden" name="country" id="country" value="<?php echo $country; ?>" />
            <input type="hidden" name="accomProvided" id="accomProvided" value="<?php echo $accomProvided; ?>" />
            <input type="hidden" name="pros" id="pros" value="<?php echo $pros; ?>" />
            <input type="hidden" name="cons" id="cons" value="<?php echo $cons; ?>" />
            <input type="hidden" name="starForm0" id="starForm0" value="<?php echo $workStars; ?>" />
            <input type="hidden" name="starForm1" id="starForm1" value="<?php echo $adminStars; ?>" />
            <input type="hidden" name="starForm2" id="starForm2" value="<?php echo $accomStars; ?>" />
            <input type="hidden" name="starForm3" id="starForm3" value="<?php echo $recompStars; ?>" />
            <br /><input type="submit" value="Submit" />
        </form>
    </div>
    <?php
    require_once('template-bottom.php');
}

function write($exists, $schoolID, $reviews, $author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars) {
    global $DBH;

    # Calculate the average of the 3 or 4 star ratings
    $averageStars = ($accomProvided == 1 ? ($workStars + $adminStars + $accomStars + $recompStars) / 4 : ($workStars + $adminStars + $recompStars) / 3);

    switch ($exists) {
        case true:
            $STH = $DBH->prepare("SELECT averageStars FROM reviews WHERE school=?");
            $STH->execute(array($schoolID));
            $total = $averageStars; # Start the total with the average stars from this new review
            $i = 0;
            while ($row = $STH->fetch()) {
                $total += $row['averageStars'];
                $i++;
            }
            $i++; # Increment the number of reviews to reflect this new one
            $schoolAverageStars = $total / $i; # Then calculate the average

            $STH = $DBH->prepare("UPDATE schools SET reviews=?, averageStars=? WHERE id=?");
            $data = array($reviews, $schoolAverageStars, $schoolID);
            $STH->execute($data);
            break;

        case false:
            $STH = $DBH->prepare("INSERT INTO schools (name, nameSound, country, type, city, citySound, reviews, averageStars) VALUES (:name, :nameSound, :country, :type, :city, :citySound, 1, :averageStars)");
            $data = array('name'=>$name, 'nameSound'=>$nameSound, 'country'=>$country, 'type'=>$type, 'city'=>$city, 'citySound'=>$citySound, 'averageStars'=>$averageStars);
            $STH->execute($data);
            $STH = $DBH->query("SELECT id FROM schools ORDER BY id DESC LIMIT 1");
            $row = $STH->fetch();
            $schoolID = $row['id'];
            break;
        }

        # Insert into "reviews" table
        $STH = $DBH->prepare("INSERT INTO reviews (school, name, author, date, accomProvided, workloadStars, adminStars, accomStars, recompStars, averageStars, pros, cons, edited) VALUES (:schoolID, :name, :author, CURDATE(), :accomProvided, :workStars, :adminStars, :accomStars, :recompStars, :averageStars, :pros, :cons, 0)");
        $data = array('schoolID'=>$schoolID, 'name'=>$name, 'author'=>$author, 'accomProvided'=>$accomProvided, 'workStars'=>$workStars, 'adminStars'=>$adminStars, 'accomStars'=>$accomStars, 'recompStars'=>$recompStars, 'averageStars'=>$averageStars, 'pros'=>$pros, 'cons'=>$cons);
        $STH->execute($data);

        # Increment reviews in the "members" table
        $STH = $DBH->prepare("UPDATE members SET reviews=reviews+1 WHERE username=?");
        $STH->execute(array($author));

        return true;
    }

    if (verify($name, $type, $country, $city, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars)) {
        if (checkExisting($author, $name, $nameSound, $type, $country, $city, $citySound, $accomProvided, $pros, $cons, $workStars, $adminStars, $accomStars, $recompStars)) {
            header("location:$_URL/user/$author");
        }
    }
