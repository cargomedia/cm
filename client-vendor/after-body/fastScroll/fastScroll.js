var fastScroll = (function() {

	// Used to track the enabling of hover effects
	var enableTimer = 0;

	/*
	 * Listen for a scroll and use that to remove
	 * the possibility of hover effects
	 */

	window.addEventListener('scroll', function() {
		clearTimeout(enableTimer);
		removeHoverClass();
		enableTimer = setTimeout(addHoverClass, 500);
	}, false);

	/**
	 * Removes the hover class from the body. Hover styles
	 * are reliant on this class being present
	 */
	function removeHoverClass() {
		if ('none' !== document.body.style.pointerEvents) {
			document.body.style.pointerEvents = 'none';
		}
	}

	/**
	 * Adds the hover class to the body. Hover styles
	 * are reliant on this class being present
	 */
	function addHoverClass() {
		document.body.style.pointerEvents = 'auto';
	}
})();
