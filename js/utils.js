// File: util.js
// General utility functions

function rowClick(url)
{
    document.location.href = url;
}

function tidySearch()
    // Tidies up the search values in search.php
{
    var search = document.getElementById("searchForm");
    var city = search.elements["city"];
    var name = search.elements["name"];

    if (city.value == "" || city.value == "City or town") {
        city.disabled="true";
    }

    if (name.value == "" || name.value == "School name") {
        name.disabled="true";
    }
}

function toggleVisibility(id)
    // Used for displaying inline help in write.php
{
    var help = document.getElementById(id);
    if (help.style.display == "block")
    {
        help.style.display="none";
    }
    else
    {
        help.style.display="block";
    }
}

function searchFocus(obj)
    // Displays default search text in index.php
{
    obj.style.color='#000000';
    obj.style.fontStyle='normal';
    if (obj.value != '') {
        obj.value = '';
    }
    return true;
}

function accomClick(checkbox)
    // Enables or disables the Accomodation section in write.php
{
    var stars = document.getElementById('star2');
    var text = document.getElementById('starUser2');
    var accomHelp = document.getElementById('accom');
    var starForm = document.getElementById('starForm2');

    if (!checkbox.checked) {
        stars.style.display = 'block';
        text.style.display = 'block';
        accomHelp.innerHTML = '[?]';
        starForm.value = '';
    }

    else {
        stars.style.display = 'none';
        text.style.display = 'none';
        accomHelp.innerHTML = '';
        starForm.value = 'null';
    }
    return true;
}

// ---- Form verification functions ---- //

function checkUsername(input) {
    // Make sure the length is OK
    if (input.length < 3 || input.length > 16) {
        document.getElementById('errorList').style.display="block";
        document.getElementById('usernameLengthError').style.display="block";
        return false;
    }

    // Make sure there are no special characters
    var specialChars = "£!@#$%^&*()+={}[]\\\';,./|\":<>?~";
    for (var i = 0; i < input.length; i++) {
        if (specialChars.indexOf(input.charAt(i)) != -1) {
            document.getElementById('errorList').style.display="block";
            document.getElementById('usernameCharError').style.display="block";
            return false;
        }
    }
    return true;
}

function checkPassword(input) {
    // Make sure the length is OK
    if (input.length < 6) {
        document.getElementById("errorList").style.display="block";
        document.getElementById('passwordError').style.display="block";
        return false;
    }
    return true;
}

function checkMatch(input1, input2) {
    if (input1 == input2) {
        return true;
    }
    document.getElementById("errorList").style.display="block";
    document.getElementById("noMatchError").style.display="block";
    return false;
}

function checkEmpty(input) {
    if(input.length == 0) {
        document.getElementById('errorList').style.display="block";
        document.getElementById('requiredError').style.display="block";
        return false;
    }
    return true;
}

function checkEmail(input) {
    // Yes, I pulled this regex off the net. So sue me :)
    var regex = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
    if (input.match(regex)) {
        return true;
    }
    document.getElementById("errorList").style.display="block";
    document.getElementById("emailError").style.display="block";
    return false;
}

function validateEdit() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('requiredError').style.display="none";

    var elements = new Array();
    elements[0] = document.getElementById('starForm0').value;
    elements[1] = document.getElementById('starForm1').value;
    elements[2] = document.getElementById('starForm3').value;
    elements[3] = document.getElementById('pros').value;
    elements[4] = document.getElementById('cons').value;

    if (document.writeReview.noAccom.checked == false) {
        elements[5] = document.getElementById('starForm2').value;
    }
    for (var item in elements) {
        if (checkEmpty(elements[item]) == false) {
            window.scrollTo(0,0);
            return false;
        }
    }
    return true;
}

function validateWrite() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('requiredError').style.display="none";

    var elements = new Array();
    elements[0] = document.getElementById('name').value;
    elements[1] = document.getElementById('type').value;
    elements[2] = document.getElementById('country').value;
    elements[3] = document.getElementById('city').value;
    elements[4] = document.getElementById('starForm0').value;
    elements[5] = document.getElementById('starForm1').value;
    elements[6] = document.getElementById('starForm3').value;
    elements[7] = document.getElementById('pros').value;
    elements[8] = document.getElementById('cons').value;

    if (document.writeReview.noAccom.checked == false) {
        elements[9] = document.getElementById('starForm2').value;
    }
    for (var item in elements) {
        if (checkEmpty(elements[item]) == false) {
            window.scrollTo(0,0);
            return false;
        }
    }
    return true;
}


function validateContact() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('requiredError').style.display="none";
    document.getElementById('emailError').style.display="none";

    for (i=0; i < document.contactForm.elements.length; i++) {
        if (checkEmpty(document.contactForm.elements[i].value) == false) {
            return false;
        } 
    }
    if (checkEmail(document.contactForm.email.value) == false) {
        return false;
    } 
    return true;
}

function validateChangePassword() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('noMatchError').style.display="none";
    document.getElementById('passwordError').style.display="none";
    document.getElementById('requiredError').style.display="none";
    document.getElementById('emailError').style.display="none";

    var New = document.changePassword.New.value;
    var repeat = document.changePassword.repeat.value;

    for (i=0; i < document.changePassword.elements.length; i++) {
        if (checkEmpty(document.changePassword.elements[i].value) == false) {
            return false;
        }
    }
    // Check the password
    if (checkPassword(New) == false) {
        return false;
    }

    if (checkMatch(New, repeat) == false) {
        return false;
    }
    return true;
}

function validateChangeEmail() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('emailError').style.display="none";
    document.getElementById('requiredError').style.display="none";
    document.getElementById('noMatchError').style.display="none";
    document.getElementById('passwordError').style.display="none";

    var email = document.changeEmail.email.value;

    if (checkEmail(email) == false) {
        return false;
    }
    return true;
}

function validateLogin() {
    document.getElementById('errorList').style.display="none";
    document.getElementById('requiredError').style.display="none";

    for (i=0; i < document.loginForm.elements.length; i++) {
        if (checkEmpty(document.loginForm.elements[i].value) == false) {
            return false;
        }
    }
    return true;
}

// Validate the sign up form function
function validateRegister()
{
    // Reset the error messages
    document.getElementById('errorList').style.display="none";
    document.getElementById('requiredError').style.display="none";
    document.getElementById('usernameLengthError').style.display="none";
    document.getElementById('usernameCharError').style.display="none";
    document.getElementById('passwordError').style.display="none";
    document.getElementById('noMatchError').style.display="none";
    document.getElementById('emailError').style.display="none";

    var username = document.register.username.value;
    var password = document.register.password.value;
    var repeat = document.register.repeat.value;
    var email = document.register.email.value;

    for (i=0; i < document.register.elements.length; i++) {
        if (checkEmpty(document.register.elements[i].value) == false) {
            return false;
        }
    }

    if (checkUsername(username) == false) {
        return false;
    }

    if (checkPassword(password) == false) {
        return false;
    }

    if (checkMatch(password, repeat) == false) {
        return false;
    }

    if (checkEmail(email) == false) {
        return false;
    }
    return true;
}
