<?php
require_once('lib/db-connect.php');
require_once('lib/utils.php');
require_once('lib/stars.lib.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');

$session = new Session();

$types = array('kindergarten','elementary school','middle school','high school','college','language school','recruiting agency');
$sorts = array('country','city','name','type','averageStars','averageStars');
$countries = array("China", "Hong Kong", "Indonesia", "Japan", "South Korea", "Singapore", "Taiwan", "Thailand", "Vietnam", "Kuwait", "Saudi Arabia", "United Arab Erimates", "Australia", "Azerbaijan", "Bangladesh", "Bhutan", "Brunei", "Cambodia", "Fiji", "India", "Kazakhstan", "Kyrgyzstan", "Laos", "Macau", "Malaysia", "Mongolia", "Myanmar", "New Zealand", "Pakistan", "Papua New Guinea", "Phillipines", "Sri Lanka", "Tajikistan", "Turkmenistan", "Uzbekistan", "Afganistan", "Bahrain", "Iran", "Iraq", "Israel", "Jordan", "Lebanon", "Qatar", "Syria", "Yemen", "Albania", "Armenia", "Austria", "Belarus", "Belgium", "Bosnia &amp; Herzegovina", "Bulgaria", "Croatia", "Cyprus", "Czech Republic", "Denmark", "Estonia", "Finland", "France", "Georgia", "Germany", "Great Britain", "Greece", "Hungary", "Iceland", "Ireland", "Italy", "Latvia", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Moldova", "Monaco", "Netherlands", "Norway", "Poland", "Portugal", "Serbia", "Romania", "Russia", "Slovakia", "Slovenia", "Spain", "Sweden", "Switzerland", "Turkey", "Ukraine", "United Kingdom", "Argentina", "Bahamas", "Belize", "Bolivia", "Brazil", "Chile", "Colombia", "Costa Rica", "Cuba", "Dominican Republic", "Ecuador", "El Salvador", "French Guiana", "Guatemala", "Guyana", "Haiti", "Honduras", "Jamaica", "Mexico", "Nicaragua", "Panama", "Paraguay", "Peru", "Puerto Rico", "Uraguay", "Venezuela", "Bermuda", "Canada", "United States of America", "Algeria", "Andorra", "Angola", "Benin", "Botswana", "Burkina Faso", "Burundi", "Cameroon", "Cape Verde", "Central African Republic", "Chad", "Congo", "Cote DIvoire", "Djibouti", "East Timor", "Egypt", "Equatorial Guinea", "Ethiopia", "Gabon", "Gambia", "Ghana", "Guinea", "Kenya", "Lesotho", "Liberia", "Libya", "Madagascar", "Malawi", "Mali", "Mauritania", "Morocco", "Mozambique", "Nambia", "Niger", "Nigeria", "Oman", "Rwanda", "Senegal", "Sierra Leone", "Somalia", "South Africa", "Sudan", "Suriname", "Swaziland", "Tanzania", "Togo", "Tunisia", "Uganda", "Zaire", "Zambia", "Zimbabwe");

// Returns the current URL. Thanks to www.webcheatsheet.com
function currentURL() {
    $pageURL = 'http://';
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function param($p) {
    # Returns the value of the $_GET parameter. If it's not
    # set, returns '%', the SQL wildcard. 
    if ($p == 'sortby' && isset($_GET[$p])) {
        return verifySort($_GET[$p]);
    } elseif ($p == 'sortby' && !isset($_GET[$p])) {
        return 'name';
    }
    elseif (isset($_GET[$p])) {
        if ($p == 'country') {
            return verifyCountry($_GET[$p]);
        } elseif ($p == 'type') {
            return verifyType($_GET[$p]);
        } else {
            return '%' . strtolower($_GET[$p]) . '%';
        }
    } else {
        return '%';
    }
}

function verifySort($p) {
    global $sorts;
    if (in_array($p,$sorts)) {
        return $p;
    } else {
        return 'name';
    }
}

function verifyCountry($p) {
    global $countries;
    if (in_array($p,$countries)) {
        return $p;
    } else {
        return '%';
    }
}

function verifyType($p) {
    global $types;
    if (in_array($p,$types)) {
        return $p;
    } else {
        return '%';
    }
}

function isSelected($param,$selection) {
    global $countries, $types, $sorts;

    if (isset($_GET[$param])) {
        $value = $_GET[$param];
        switch ($param) {
            case 'country':
            if (in_array($value,$countries) && $value == $selection) {
                return 'selected';
            } else {
                return '';
            }
            case 'type':
            if (in_array($value,$types) && $value == $selection) {
                return 'selected';
            } else {
                return '';
            }
            case 'sortby':
            if (in_array($value,$sorts) && $value == $selection) {
                return 'selected';
            } else {
                return '';
            }
        }
    } elseif (!isset($_GET[$param]) && $selection == 'default') {
        return 'selected';
    } else {
        return '';
    }
}

function inputValue($param) {
    # Sets the value of the text inputs according to the $_GET params
    if (isset($_GET[$param])) {
        if ($param == 'name' && $_GET[$param] == '') {
            return html('School name');
        }
        return $_GET[$param];
    } else {
        switch ($param) {
            case 'city':
            return html('City or town');
            case 'name':
            return html('School name');
        }
    }
}

function desc() {
    if (isset($_GET['desc'])) {
        return '';
    } else {
        return '&amp;desc=1';
    }
}

function sortBy($p) {
    $url = currentURL();
    $find = strpos($url, 'sortby');
    $sortby = verifySort($p);
    $desc = desc();
    if ($find === false) {
        $q = strpos($url, '?');
        if ($q === false) {
            return "$url?sortby=$sortby$desc";
        } else { 
            return "$url&amp;sortby=$sortby$desc";
        }
    } else {
        $base = substr($url, 0, $find);
        return $base . "sortby=$sortby$desc";
    }
}

function buildQuery() {
    $query = array('sql'=>'', 'params'=>array(':country'=>param('country'), ':type'=>param('type')));
    $main = 'SELECT * FROM schools WHERE country LIKE :country AND type LIKE :type';
    $sortby = ' ORDER BY ' . param('sortby');
    if (isset($_GET['desc'])) {
        $sortby .= ' DESC';
    }
    if (! $_GET) {
        $query['sql'] = $main . $sortby;
        return $query;
    } elseif (isset($_GET['city']) && ! isset($_GET['name'])) {
        $query['sql'] = $main . ' AND (city LIKE :city OR citySound = :citySound)' . $sortby;
        $query['params'][':city'] = param('city');
        $query['params'][':citySound'] = soundex(param('city'));
        return $query;
    } elseif (isset($_GET['name']) && ! isset($_GET['city'])) {
        $query['sql'] = $main . ' AND (name LIKE :name OR nameSound = :nameSound)' . $sortby;
        $query['params'][':name'] = param('name');
        $query['params'][':nameSound'] = soundex(param('city'));
        return $query;
    } else {
        $query['sql'] = $main . ' AND ((city LIKE :city OR citySound = :citySound) OR (name LIKE :name OR nameSound = :nameSound))' . $sortby;
        $query['params'][':city'] = param('city');
        $query['params'][':citySound'] = soundex(param('city'));
        $query['params'][':name'] = param('name');
        $query['params'][':nameSound'] = soundex(param('city'));
        return $query;
    }
}

function arrow($p) {
    if (!isset($_GET['sortby']) || $_GET['sortby'] != $p) {
        echo '';
    } else {
        if (isset($_GET['desc'])) {
            echo '<span class="sortBy">&nbsp;&nbsp;&nbsp</span>';
        } else {
            echo '<span class="sortBy" style="background-position: 0px 8px;">&nbsp;&nbsp;&nbsp</span>';
        }
    }
}

$page = new Page('Find Schools');
$page->render_header();

?>

<div id="midBar">
    <h1>Find Schools</h1><br />
    <form id="searchForm" action="search" onsubmit="return tidySearch()" method="get">
        <select class="select" name="country">
            <option <?php echo isSelected('country','any'); ?> value="any" >Any Country</option>
            <?php require_once('countries.html'); ?>
        </select>
        <select class="select" name="type">
            <option <?php echo isSelected('type','any'); ?> value="any" >Any school type</option>
            <option <?php echo isSelected('type','kindergarten'); ?> value="kindergarten">Kindergarten</option>
            <option <?php echo isSelected('type','elementary school'); ?> value="elementary school">Elementary School</option>
            <option <?php echo isSelected('type','middle school'); ?> value="middle school">Middle School</option>
            <option <?php echo isSelected('type','high school'); ?> value="high school">High School</option>
            <option <?php echo isSelected('type','college'); ?> value="college">University/College</option>
            <option <?php echo isSelected('type','language school'); ?> value="language school">Language School</option>
            <option <?php echo isSelected('type','recruiting agency'); ?> value="recruiting agency">Recruiting Agency</option>
        </select><br />
        <input type="text" class="text" name="city" onfocus="searchFocus(this)" value="<?php echo html(inputValue('city')); ?>" />
        <input type="text" class="text" name="name" onfocus="searchFocus(this)" value="<?php echo html(inputValue('name')); ?>" /><br />
        Sort by:<select class="select" name="sortby">
            <option <?php echo isSelected('sortby','name'); ?> value="name" >School name</option>
            <option <?php echo isSelected('sortby','city'); ?> value="city" >City</option>
            <option <?php echo isSelected('sortby','country'); ?> value="country" >Country</option>
            <option <?php echo isSelected('sortby','type'); ?> value="type" >School type</option>
            <option <?php echo isSelected('sortby','averageStars'); ?> value="averageStars" >Rating</option>
        </select><br />
        <input id="submit" type="submit" value="Find Schools" />
        <input type="reset" value="Reset" />
    </form>

    <?php 
    echo '<hr /><br />';
    $query = buildQuery();
    $STH = $DBH->prepare($query['sql']);
    $STH->execute($query['params']);
    $n = 0;
    while ($STH->fetch()) {
        $n++;
    }
    if ($n == 0) { ?>
        <h3>So sorry, but no reviews were found! Try broadening your search criteria.</h3><br />
        <?php if (isset($_GET['name']) && isset($_GET['country']) && isset($_GET['type']) && isset($_GET['city'])) {
            if (!isset($_SESSION))
                session_start();
            $_SESSION['write_info'] = array();
            $_SESSION['write_info']['name'] = $_GET['name'];
            $_SESSION['write_info']['type'] = $_GET['type'];
            $_SESSION['write_info']['city'] = $_GET['city'];
            $_SESSION['write_info']['country'] = $_GET['country'];
            echo '<a href="write"><h3>Be the first to write a review for this school!</h3></a><br /></form>';
    }
} else { ?>
    <h3><?php echo $n . ' School';
        if ($n != 1) { echo 's'; } ?> Found</h3><br />
    <table class="reviewTable" width="100%" cellpadding="5px">
        <thead class="reviewTableHead" align="center" valign="top">
            <tr class="reviewTableHead" >
                <th class="reviewTableHead"><a href="<?php echo sortBy('name'); ?>" title="Sort by school name">School</a><?php arrow('name') ?></th>
                <th class="reviewTableHead"><a href="<?php echo sortBy('city'); ?>" title="Sort by city">City</a><?php arrow('city'); ?></th>
                <th class="reviewTableHead"><a href="<?php echo sortBy('country'); ?>" title="Sort by country">Country</a><?php arrow('country') ?></th>
                <th class="reviewTableHead"><a href="<?php echo sortBy('type'); ?>" title="Sort by school type">Type</a><?php arrow('type'); ?></th>
                <th class="reviewTableHead"><a href="<?php echo sortBy('averageStars'); ?>" title="Sort by rating">Rating</a><?php arrow('averageStars'); ?></th>
            </tr>
        </thead>
        <tbody class="reviewTable">
            <?php
            $stars = new Stars();
            $stars->set_centered();
            $stars->set_adjustable(false);
            $STH->execute($query['params']);
            $i = 0;
            while ($row = $STH->fetch()) {
                $id = $row['id'];
                $name = html($row['name']);
                $country = html($row['country']);
                $type = ucwords(html($row['type']));
                $city = html($row['city']);
                $stars->set_rating($row['averageStars']);
                echo "
                <tr id='searchTable' onclick='rowClick(\"school/$school->get_url()\")' class='reviewTable'>
                    <td class='reviewTable'>$name</td>
                    <td class='reviewTable'>{ucwords($city)}</td>
                    <td class='reviewTable'>$country</td>
                    <td class='reviewTable'>$type</td>
                    <td class='reviewTable'>$stars->show_stars()</td>
                </tr>";
                $i++;
            }
            echo '</tbody></table>';
}
echo '</div> <!-- Closes midBar -->';
$page->render_footer();
?>
