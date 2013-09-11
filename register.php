<?php 

require_once('lib/page.lib.php');
require_once('lib/session.lib.php');

$page = new Page('Register');
$session = new Session();

$page->render_header();

try {
    $form_data = $session->get_form_data();
}
catch(SessionException $exception) {
    // If this is thrown, it's a normal case, so suppress it!
}

?>

<div id="midBar">

    <!-- Set the tabindex of the reCaptcha -->
    <script type="text/javascript"> 
        var RecaptchaOptions = { tabindex : 5 };
    </script>

    <div id="form">
        <h2 style="text-align:left;">Register as a new user</h2><br />
        <form class="defaultForm" name="register" onsubmit="return validateRegister()" action="hdl/register.hdl.php" method="post">
            <?php echo $session->get_error(); ?><br /><br />
            <ul class="error" id="errorList">
                <li class="error" id="requiredError">Please fill in all the fields.</li>
                <li class="error" id="usernameLengthError">Your username must be between 4 and 16 characters.</li>
                <li class="error" id="usernameCharError">Your username may only contain alphanumeric characters, dashes ( - ), and underscores ( _ )</li>
                <li class="error" id="passwordError">Your password must be at least 6 characters long.</li>
                <li class="error" id="noMatchError">Your passwords don't match!</li>
                <li class="error" id="emailError">Please enter a valid e-mail address.</li>
            </ul>
            <label for="username">Username (between 3 and 16 characters)</label><br />
            <input type="text" name="username" tabindex="1" value="<?php echo $form_data['username']; ?>" /><br />
            <label for="email">E-mail</label><br />
            <input type="text" name="email" tabindex="2" value="<?php echo $form_data['email']; ?>" /><br />
            <label for="password">Password (at least 6 characters)</label><br />
            <input type="password" name="password" tabindex="3" value="<?php echo $form_data['password']; ?>" /><br />
            <label for="repeat">Repeat Password</label><br />
            <input type="password" name="repeat" tabindex="4" value="<?php echo $form_data['repeat']; ?>" /><br />

            <?php require_once('lib/recaptcha.lib.php');
            $publickey = '6LfntsMSAAAAAC3S1YLlkFFl5BjRCZxaMgL22nTQ';
            echo recaptcha_get_html($publickey); ?>

            <br /><input type="submit" value="Submit" tabindex="6" />
        </form>
    </div>
    <div id="alternateLogin">
        <h2 style="text-align:left;">Or, simply login with:</h2><br />
        <a href="facebook-login"><img src="img/facebook-logo.png" alt="Login using Facebook" /></a><br /><br />
        <a href="google-login"><img src="img/google-logo.png" alt="Login using your Google Account" /></a>
        <a href="yahoo-login"><img class="login" src="img/yahoo-logo.png" alt="Login using your Yahoo Account" /></a>
    </div>
</div>

<?php $page->render_footer(); ?>
