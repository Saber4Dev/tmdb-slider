/**
 * TMDB Slider JavaScript
 *
 * Handles dynamic speed settings and poster width.
 *
 * @package TMDB_Slider
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Initialize sliders
	 */
	function initSliders() {
		// Hero sliders
		const heroSliders = document.querySelectorAll('.tmdb-hero-slider[data-speed]');
		heroSliders.forEach(function(slider) {
			const speed = slider.getAttribute('data-speed');
			if (speed) {
				slider.style.setProperty('--speed', speed);
			}
		});

		// Row sliders
		const rowSliders = document.querySelectorAll('.tmdb-row-slider[data-speed]');
		rowSliders.forEach(function(slider) {
			const speed = slider.getAttribute('data-speed');
			const posterWidth = slider.getAttribute('data-poster-width');
			
			if (speed) {
				slider.style.setProperty('--speed', speed);
			}

			// Set poster width
			if (posterWidth) {
				slider.style.setProperty('--poster-width', posterWidth + 'px');
			}
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSliders);
	} else {
		initSliders();
	}
})();

