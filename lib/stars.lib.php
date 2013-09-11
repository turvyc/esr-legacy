<?php

/** stars.lib.php

Interface for displaying the star rating graphics on the page.

*/

class Stars {

    const MIN_RATING = 0;
    const MAX_RATING = 100;
    const PIXEL_WIDTH = 0.84;

    // ID values
    const WORK = 0;
    const ADMIN = 1;
    const ACCOM = 2;
    const RECOMP = 3;

    const WORK_VALUE = 'starForm0';
    const ADMIN_VALUE = 'starForm1';
    const ACCOM_VALUE = 'starForm2';
    const RECOMP_VALUE = 'starForm3';

    private $id;            // Which category the stars represent
    private $value;         // The rating out of 100 for the stars
    private $centered;      // Boolean. Center-align the stars when displaying on the page? 
    private $adjustable;    // Boolean. Can users adjust the star field? 
    private $text;          // The text displayed to the right of the stars 

    // Constructs a new star. The default values are the values needed for
    // write.php, in order to minimize the amount of PHP needed in that file.
    public function __construct($id, $value=null, $centered=false, $adjustable=true) {
        $this->id = $id;
        $this->centered = $centered;
        $this->adjustable = $adjustable;
        $this->value = $value;
        $this->text = ($value == null) ? 'Give a Rating!' : $this->text = $value;
    }

    // If the value is set, we also want it to be displayed beside the stars.
    public function set_value($value) {
        if ($value < Stars::MIN_RATING || $value > Stars::MAX_RATING) {
            throw new Exception("Star value out of range.");
        }
        $this->value = $value;
        $this->text = $value;
    }

    public function set_adjustable($bool = true) {
        $this->adjustable = $bool;
    }

    public function set_centered($bool = true) {
        $this->centered = $bool;
    }

    // Generates the HTML required to display the stars.
    public function to_html() {
        if ($this->adjustable) {
            $style = '"float:left;"';
            $text = "<div style='color:rgb(136,136,136);' id='text$this->id' 
                class='user'>$this->text</div>\n";
            $js = 'onmousedown="star.update(event,this)" 
                onmousemove="star.mouse(event,this)"';
            $input = "<input type='hidden' id='starForm$this->id' 
                name='starForm$this->id' value='$this->value' /><br />";
        }
        else {
            $text = $js = $input = '';
            $style = 'cursor:default;';
            if ($this->centered) {
                $style .= 'margin-right:auto;margin-left:auto;';
            }
        }
        $width = round($this->value * Stars::PIXEL_WIDTH) . 'px';
        return "
        <div class='star'>
            <ul id='star$this->id' class='star' style='$style' $js title='$this->text'>
                <li id='starCur$this->id' class='curr' title='$this->value' style='width:$width'></li>
            </ul>$text
        </div>
        <br style='clear:both;' />
        $input\n";
    }
}

?>
