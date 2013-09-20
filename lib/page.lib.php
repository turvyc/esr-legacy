<?php

/** page.lib.php

Codifies the mechanism for displaying the header and footer of every page.

**/

require_once('session.lib.php');

class Page {

    private $authorized; // Boolean
    private $title;      // String
    private $username;   // String

    // The member variables are initialized using information from the Session
    // object.
    public function __construct($title) {
        require('lib/settings.lib.php');
        if ($_DEBUG) {
            error_reporting(E_ALL);
        }

        $session = new Session();
        $this->title = $title;
        $this->authorized = $session->is_authorized();
        $this->username = ($this->authorized) ? $session->get_username() : '';
    }

    // Outputs the HTML page header.
    // I know that it is considered bad design to (a) switch between HTML and
    // PHP in the same file and (b) output HTML directly from an object, but I
    // think in this case it's the best option, because the header has several
    // dynamic components which require switching in and out of PHP and HTML,
    // and using EOF or strings create an unmaintainable mess of HTML.
    public function render_header() { 
        require('lib/settings.lib.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $this->title; ?> -- ESL School Rater</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <base href="<?php echo $_URL; ?>/;" />
        <meta name="Keywords" content="efl,esl,school,rate,rater,rating,review,teach,teacher,english,korea,japan,china,taiwan" />
        <meta name="Description" content="Read and write reviews of ESL and EFL schools." />
        <script type="text/javascript" src="js/utils.js"></script>
        <script type="text/javascript" src="js/stars.js"></script>
        <link rel="stylesheet" type="text/css" href="css/reset.css" /> 
        <link rel="stylesheet" type="text/css" href="css/main.css" />
        <!--[if lte IE 8]>!h!I
        <link rel="stylesheet" type="text/css" href="css/ie.css" /> 
        <script type="text/javascript" src="js/ie-stars.js"></script>
        <![endif]-->
        <link rel="icon" type="image/png" href="img/favicon.png" />
    </head> 
    <?php flush(); ?>
    <body>
        <?php #include_once('analyticstracking.php'); ?>
        <div id="container">
            <!--[if lte IE 6]>
            <img id="ie6banner" src="img/ie6-banner.png" width="600px" height="150px" alt="You are using a crappy old browser, and in the name of the Internet I refuse to support it. Get with the times and go download Firefox or something: http://www.getfirefox.com" usemap="#ie6bannerMap" />
            <map name="ie6bannerMap">
                <area shape="rect" coords="45,95,215,140" href="http://www.getfirefox.com" />
                <area shape="rect" coords="216,95,390,140" href="http://www.google.com/chrome" />
                <area shape="rect" coords="391,95,565,140" href="http://www.opera.com/download" />
            </map>
            <![endif]-->
            <div id="header">
                <div id="logo">
                    <h1><a href="/<?php echo $_DOC_ROOT; ?>"><img title="ESL School Rater" border="none" src="img/logo.png" alt="ESL School Rater" width="375" height="60" /></a></h1>
                </div>
                <div id="search">
                    <form id="headerSearch" method="get" action="search">
                        <input id="headerSubmit" type="submit" value="" />
                        <span>
                            <input class="inputText" id="headerInput" type="text" name="name" value="Enter a school's name" onfocus="searchFocus(this)" />
                        </span>
                    </form>
                </div>
            </div>
            <div id="content">
                <div class="prop"></div>
                <div id="navbar">
                    <ul>
                        <li class="nav1"><a href="search">Find Schools</a></li>
                        <li class="nav"><a href="write">Rate a School</a></li>
                        <li class="nav"><a href="contact">Contact</a></li>
                        <?php
                        if ($this->authorized) { ?>
                        <li id="greeting">Welcome, <a href="user/<?php echo $this->username; ?>"><?php echo $this->username; ?>!</a> [<a style="font-variant:small-caps;" href="logout">logout</a>]</li>
                            <?php
                        } else { 
                            ?>
                            <li id="greeting">Welcome, Guest! [<a href="register">register</a> | <a href="login">login</a>]</li>
                            <?php } ?>
                        </ul>
                    </div> <!-- Closes navbar -->
                    <hr />
                    <?php 
    }

    // Outputs the HTML page footer.
    public function render_footer() { ?>
   </div> <!-- Closes content -->
            <div id="footer">
                &copy; 2012 T. Colin Strong | <a href="contact">Contact</a> 
             </div> <!-- Closes footer -->
          </div> <!-- Closes container -->
   </body>
</html> <?php
    }
}








