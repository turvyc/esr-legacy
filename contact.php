<?php

/** contact.php

Creates a simple web form for user feedback.

**/

require_once('lib/db-connect.php');
require_once('lib/session.lib.php');
require_once('lib/page.lib.php');

$session = new Session();
$page = new Page('Contact');
$page->render_header();

// In the case that the user fails server-side validation, they would find it
// nice to find all their information remembered. inputValue and isSelected
// provide that functionality by returning the appropriate value from POST.
function inputValue($param) {
    if (isset($_POST[$param])) {
        return $_POST[$param];
    } else {
        return '';
    }
}

function isSelected($selection) {
    $valid = array('bug', 'suggestion', 'question', 'love', 'hate', 'other');
    if (!isset($_POST['subject']) && $selection == 'default') {
        return 'selected="selected"';
    } elseif (isset($_POST['subject'])) {
        $value = $_POST['subject'];
        if (in_array($value,$valid) && $value == $selection) {
            return 'selected="selected"';
        }
    }
    return '';
}

?>
<div id="midBar">
    <form class="defaultForm" name="contactForm" onsubmit="return validateContact()" action="hdl/contact.hdl.php" method="post">
        <h1>Contact</h1><br />
        <?php echo $session->get_error(); ?>
        <ul class="error" id="errorList">
            <li class="error" id="requiredError">Please complete all the fields.</li>
            <li class="error" id="emailError">Please enter a valid e-mail address.</li>
        </ul>
        <label for="name">Your name</label><br />
        <input value="<?php echo inputValue('name'); ?>" type="text" name="name" /><br />
        <label for="email">Email</label><br />
        <input value="<?php echo inputValue('email'); ?>" type="text" name="email" /><br />
        <select name="subject"> 
            <option disabled="disabled" value="default" <?php echo isSelected('default'); ?>>Choose a subject</option>
            <option value="bug" <?php echo isSelected('bug'); ?>>Bug report</option>
            <option value="suggestion" <?php echo isSelected('suggestion'); ?>>Suggestion</option>
            <option value="question" <?php echo isSelected('question'); ?>>Question</option>
            <option value="love" <?php echo isSelected('love'); ?>>Love mail</option>
            <option value="hate" <?php echo isSelected('hate'); ?>>Hate mail</option>
            <option value="other" <?php echo isSelected('other'); ?>>Uncategorizable</option>
        </select><br />
        <textarea name="message" cols="56" rows="10"><?php echo inputValue('message'); ?></textarea><br />
        <input name="submit" type="submit" value="Send" />
    </form>
</div>

<?php $page->render_footer(); ?>
