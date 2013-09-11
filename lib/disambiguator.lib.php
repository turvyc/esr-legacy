<?php

require('school.lib.php'); // Needed for the constants

class Disambiguator {

    private $names;
    private $cities;

    public function __construct($similarities) {
        $names = $similarities[School::NAME];
        $cities = $similarities[School::CITY];
    }

    // Returns an appropriate title, depending on what is being disambiguated.
    public function generate_title() {
        if (! empty($names) && ! empty($cities)) {
            return 'cities and school names';
        }
        elseif (! empty($cities)) {
            return 'cities';
        }
        else {
            return 'school names';
        }
    }

    // Returns the html to display the disambiguation choices to the user
    public function display_html() {
        $html = '';

        if (! empty($this->cities)) {
            $html .= "<h3 style=\"text-align:left;\">Cities</h3>";
            $html .= $this->generate_radio_buttons($this->cities, School::CITY);
        }

        if (! empty($this->names)) {
            $html .= "<h3 style=\"text-align:left;\">School Names</h3>";
            $html .= $this->generate_radio_buttons($this->names, School::NAMES);
        }

        return $html;
    }

    // Returns an HTML radio-button list of the alternative cities or names.
    private function generate_radio_buttons($choices, $type) {
        $html = '';
        $choices = array_unique($choices);

        foreach ($choices as $choice) {
            $html .= "<input type='radio' name='$type' value='$choice' /> 
            {ucwords($choice)} <br />\n";
        }

        $original = $_SESSION['school_data'][$type];
        $html .= '<input type="radio" name="name" value="' .  $original . 
        '"> Nope, it\'s not any of these. <br /><br />';
    }

}

?>
