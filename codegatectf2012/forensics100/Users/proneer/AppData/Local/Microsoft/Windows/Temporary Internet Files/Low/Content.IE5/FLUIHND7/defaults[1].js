function css3support() {
	var testElement = document.createElement('test');
	testElement.setAttribute('style', '-moz-box-shadow:#000 1px 1px 3px;');
	document.body.appendChild(testElement);
        var testStyles = testElement.style;
	if (!!testStyles.mozBoxShadow || !!testStyles.MozBoxShadow) {
		return false;
	} 
	else {
		return true;
	}
}

// Make elements for IE
Event.onDOMReady(function() {
	var elements = ['article', 'nav', 'section', 'header', 'aside', 'footer'];
	elements.each(function(el) {
		document.createElement(el);
	});

	if (AC.Detector.isIE()) {
		if ($('whatis')) {
			if ($$('.buckets')) {
				$$('.buckets a')[0].addClassName('first');
			}
		}
	}
});
