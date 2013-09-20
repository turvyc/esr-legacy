<?php

/** login.php

Provides a login form and links to the web-service logins

Calls: login.hdl.php

**/

require_once('lib/settings.lib.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');

$session = new Session();
$page = new Page('Login');

$page->render_header();

?>

<div id="midBar">
    <div id="form">
        <h2 style="text-align:left;">ESL School Rater Login</h2><br />
        <form class="defaultForm" name="loginForm" onsubmit="return validateLogin()" action="hdl/login.hdl.php" method="post">
            <?php echo $session->get_error(); ?>
            <ul class="error" id="errorList">
                <li class="error" id="requiredError">I recommend filling in <i>both</i> your username and password : )</li>
            </ul>
            <br />
            <label for="username">Username:</label><br />
            <input type="text" name="username" tabindex="1" /><br />
            <label for="password">Password:</label><br />
            <input type="password" name="password" tabindex="2" /><br />
            <a href="forgot" style="text-decoration:none;"><span class="small">forgot your password?</span></a><br /><br />
            <label for="remember">Stay logged in:</label>
            <input type="checkbox" name="remember" value="true" tabindex="3" /><br />
            <input type="submit" value="Submit" tabindex="4" />
        </form>
    </div>
    <div id="alternateLogin">
        <h2 style="text-align:left;">Login with . . .</h2><br />
        <a href="facebook-login"><img class="login" src="img/facebook-logo.png" alt="Login using Facebook" /></a>
        <a href="google-login"><img class="login" src="img/google-logo.png" alt="Login using your Google Account" /></a>
        <a href="yahoo-login"><img class="login" src="img/yahoo-logo.png" alt="Login using your Yahoo Account" /></a>
    </div>
</div>

<?php $page->render_footer(); ?>
