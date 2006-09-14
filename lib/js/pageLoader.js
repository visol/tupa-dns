function pageLoader(state) {
	var x, y;
	// Get actual size for message position
	if (document.documentElement.clientHeight) { // IE6
		x = document.documentElement.clientWidth;
		y = document.documentElement.clientHeight;
	} else if (document.body.clientHeight) { // other IE's
		x = document.body.clientWidth;
		y = document.body.clientHeight;
	} else { // all/most other browsers
		x = window.innerWidth;
		y = window.innerHeight;
	}

	// Make the transparente layer visible to prevent clicking other links
	var elem = document.getElementById('pageloader-trans');
	if(elem != null) {
		// Set the layer width, height, zIndex
		elem.style.width = x + 'px';
		elem.style.height = y + 'px';
		elem.style.zIndex = 98;
		// Make it visible
		elem.style.visibility = (state == 1) ? 'visible' : 'hidden';
		elem.style.display = (state == 1) ? 'block' : 'none';
	}

	// Same with the loading message
	var elem = document.getElementById('pageloader-msg');
	if(elem != null) {
		// callculate the position to place it more or less in the middle
		var top = (y/2) - 80;
		var left = (x/2) - 150;
		if(top < 0) top = 0;
		if(left < 0) left = 0;

		// Set the layer width, height, zIndex
		elem.style.left = left + 'px';
		elem.style.top = top + 'px';
		elem.style.zIndex = 99;
		// Make it visible
		elem.style.visibility = (state == 1) ? 'visible' : 'hidden';
		elem.style.display = (state == 1) ? 'block' : 'none';
	}
}