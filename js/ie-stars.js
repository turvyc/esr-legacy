// File: stars.js
// Info: Functions to handle dynamic star rating system
// Shamelessly borrowed from http://nofunc.org/AJAX_Star_Rating/ 

function $(v,o) 
// Returns the element with the ID of 'v', either within the object 'o' or the document, if 'o' is not an object
{ 
    // 'o' is an optional argument which defaults to 'document'
    return ((typeof(o) == 'object' ? o : document).getElementById(v)); 
}

function $S(o) 
// Returns the style of the object or element 'o', where the element is determined by function $().
{ 
    return ((typeof(o) == 'object' ? o : $(o)).style); 
}

function agent(v) 
// Returns a positive number (evaluating to True) if string 'v' is found
// in the userAgent string. Returns 0 (evaluating to False) if it's not found.
{ 
    return (Math.max(navigator.userAgent.toLowerCase().indexOf(v),0)); 
}

function abPos(o) 
// Returns the absolute position of the object.
{ 
    var o = (typeof(o) == 'object' ? o : $(o)), z = {X:0,Y:0}; 
    while (o != null) 
    { 
        z.X += o.offsetLeft;
        z.Y += o.offsetTop; 
        o = o.offsetParent;
    }; 
    return(z); 
}

function XY(e,v) 
{ 
    // Test for IE
    var o = agent('msie') ?  { 'X':event.clientX + document.body.scrollLeft, 'Y':event.clientY + document.body.scrollTop } : { 'X' : e.pageX, 'Y' : e.pageY };
    // 'o' is now an Object Literal containing the X and Y coordinates of the current mouse position
        return(v ? o[v] : o);
}

star={};

star.mouse = function(e,o) 
{ 
    if (star.stop || isNaN(star.stop)) 
    { 
        star.stop=0;
        document.onmousemove = function(e) // Overriding the default onmousemove function
        { 
            var n = star.num;
            // oX and oY are mouse position minus object position.
            var p = abPos( $('star' + n) ), x = XY(e), oX = x.X-p.X, oY = x.Y-p.Y;
            star.num = o.id.substr(4);

            // If the mouse position is OUTSIDE of the object position
		    if (oX<1 || oX>84 || oY<0 || oY>19) 
            { 
                star.stop = 1; 
                star.revert(); 
            }
		
            // The mouse position is INSIDE the object position
		    else 
            {

			    $S('starCur'+n).width = Math.round(oX) + 'px'; // Set starCur to the width of the mouse position
			    $S('starUser'+n).color = '#111'; // Set the digit color to dark gray
			    $('starUser'+n).innerHTML = Math.round(oX / 84 * 100 / 10)*10 + '%'; // Set the text of the percent digit
		    }
	    };
    } 
};

star.update = function(e,o) 
{
    // 'v' is the digit text
    var n = star.num, v = parseInt($('starUser' + n).innerHTML);
    n = o.id.substr(4);

    // Set the title of starCur to 'v'
    $('starCur' + n).title = (v >= 0 ? v : -1);

    // Set the value of the hidden input in the form
    $('starForm' + n).value = v;
};

star.revert = function() 
{ 
    // 'v' is the integer in the "title" attribute of the li "starCurX"
    var n = star.num, v = parseInt($('starCur' + n).title);

    // Set the width of starCur to 'v'
	$S('starCur'+n).width = (v >= 0 ? Math.round(v * 84/100) + 'px' : '0px');

    // Set the digit to 'v', or nothing if 'v' is 0
	$('starUser' + n).innerHTML = (v >= 0 ? Math.round(v) + '%' : 'Give a Rating');

    // Change the digit color to light gray
	$('starUser' + n).style.color = '#666';

    // Don't do anything if the mouse is moving somewhere else
	document.onmousemove = '';
};

star.num = 0;

