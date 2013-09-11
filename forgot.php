<?php
require_once('lib/page.lib.php');
require_once('lib/session.lib.php');

$session = new Session();
$page = new Page('Forgotten Password');
$page->render_header();
?>

<div id="midBar">
    <h2>Reset Your Password</h2><br />
    <?php echo $session->get_error(); ?>
    <p>If you have forgotten your password, enter the e-mail address you used 
    to register and we will reset your password to a random value and send it 
    to you. Then you can login and change your password to whatever you like.</p><br />
    <form class="defaultForm" action="hdl/forgot.hdl.php" method="post">
        <label for="email">Your E-mail</label><br />
        <input type="text" name="email" tabindex="1" /><br />
        <input type="submit" value="Submit" />
    </form>
</div>

<?php $page->render_footer(); ?>
