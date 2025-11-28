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
			const reverse = slider.getAttribute('data-reverse') === '1';
			const stopOnHover = slider.getAttribute('data-stop-on-hover') === '1';
			const track = slider.querySelector('.tmdb-hero-slider-track');
			
			if (speed) {
				slider.style.setProperty('--speed', speed);
			}

			// Apply reverse direction
			if (reverse) {
				track.style.animationDirection = 'reverse';
			}

			// Handle stop on hover
			if (stopOnHover && track) {
				slider.addEventListener('mouseenter', function() {
					track.style.animationPlayState = 'paused';
				});
				slider.addEventListener('mouseleave', function() {
					track.style.animationPlayState = 'running';
				});
			} else if (track) {
				// Remove hover pause if disabled
				track.style.animationPlayState = 'running';
			}
		});

		// Row sliders
		const rowSliders = document.querySelectorAll('.tmdb-row-slider[data-speed]');
		rowSliders.forEach(function(slider) {
			const speed = slider.getAttribute('data-speed');
			const posterWidth = slider.getAttribute('data-poster-width');
			const reverse = slider.getAttribute('data-reverse') === '1';
			const stopOnHover = slider.getAttribute('data-stop-on-hover') === '1';
			const track = slider.querySelector('.tmdb-row-slider-track');
			
			if (speed) {
				slider.style.setProperty('--speed', speed);
			}

			// Set poster width
			if (posterWidth) {
				slider.style.setProperty('--poster-width', posterWidth + 'px');
			}

			// Apply reverse direction
			if (reverse) {
				track.style.animationDirection = 'reverse';
			}

			// Handle stop on hover
			if (stopOnHover && track) {
				slider.addEventListener('mouseenter', function() {
					track.style.animationPlayState = 'paused';
				});
				slider.addEventListener('mouseleave', function() {
					track.style.animationPlayState = 'running';
				});
			} else if (track) {
				// Remove hover pause if disabled
				track.style.animationPlayState = 'running';
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

