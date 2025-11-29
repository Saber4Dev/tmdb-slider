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
			const posterGap = slider.getAttribute('data-poster-gap');
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

			// Set poster gap
			if (posterGap) {
				slider.style.setProperty('--poster-gap', posterGap + 'px');
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

	/**
	 * Initialize dynamic backgrounds
	 */
	function initBackgrounds() {
		if (typeof tmdbSlider === 'undefined') {
			return;
		}

		const bgSettings = tmdbSlider.bgSettings || {};
		const apiUrl = tmdbSlider.apiUrl || '';
		const nonce = tmdbSlider.nonce || '';

		// Find all elements with background IDs
		const backgroundPattern = /^tmdb--(.+?)-(movie|tv)-background$/;
		const backgroundElements = document.querySelectorAll('[id^="tmdb--"][id$="-background"]');

		backgroundElements.forEach(function(element) {
			const id = element.id;
			const match = id.match(backgroundPattern);

			if (!match) {
				return;
			}

			const category = match[1];
			const type = match[2];

			// Apply base styles
			element.style.position = 'relative';
			element.style.overflow = 'hidden';

			// Add overlay if enabled (will be added after background layers)
			// Store overlay settings for later
			const needsOverlay = bgSettings.overlay;
			const overlayColor = bgSettings.overlayColor || '#000000';

			// Fetch background images
			fetch(apiUrl + category + '/' + type, {
				method: 'GET',
				headers: {
					'X-WP-Nonce': nonce
				}
			})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(function(backgrounds) {
				if (!backgrounds || backgrounds.length === 0) {
					return;
				}

				let currentIndex = 0;

				// Create background layers for smooth transitions
				const bgLayer1 = document.createElement('div');
				const bgLayer2 = document.createElement('div');
				bgLayer1.className = 'tmdb-bg-layer';
				bgLayer2.className = 'tmdb-bg-layer';
				bgLayer1.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-position: ' + (bgSettings.position || 'center') + '; background-size: ' + (bgSettings.size || 'cover') + '; background-repeat: no-repeat; opacity: 1; transition: opacity 1s ease-in-out; z-index: 0;';
				bgLayer2.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-position: ' + (bgSettings.position || 'center') + '; background-size: ' + (bgSettings.size || 'cover') + '; background-repeat: no-repeat; opacity: 0; transition: opacity 1s ease-in-out; z-index: 0;';

				// Set initial background
				if (backgrounds[0] && backgrounds[0].url) {
					bgLayer1.style.backgroundImage = 'url(' + backgrounds[0].url + ')';
				}
				element.appendChild(bgLayer1);
				element.appendChild(bgLayer2);

				// Add overlay if enabled (above background layers, below content)
				if (needsOverlay) {
					let overlay = element.querySelector('.tmdb-bg-overlay');
					if (!overlay) {
						overlay = document.createElement('div');
						overlay.className = 'tmdb-bg-overlay';
						element.appendChild(overlay);
					}
					overlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: ' + overlayColor + '; opacity: 0.5; z-index: 1; pointer-events: none;';
				}

				// Change background at interval
				const changeInterval = (bgSettings.changeInterval || 5) * 1000;
				setInterval(function() {
					currentIndex = (currentIndex + 1) % backgrounds.length;
					if (backgrounds[currentIndex] && backgrounds[currentIndex].url) {
						// Determine which layer is currently visible
						const currentLayer = bgLayer1.style.opacity === '1' ? bgLayer1 : bgLayer2;
						const nextLayer = bgLayer1.style.opacity === '1' ? bgLayer2 : bgLayer1;

						// Set new background on hidden layer
						nextLayer.style.backgroundImage = 'url(' + backgrounds[currentIndex].url + ')';
						
						// Fade transition
						currentLayer.style.opacity = '0';
						nextLayer.style.opacity = '1';
					}
				}, changeInterval);
			})
			.catch(function(error) {
				console.error('TMDB Slider: Error fetching backgrounds', error);
			});
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			initSliders();
			initBackgrounds();
		});
	} else {
		initSliders();
		initBackgrounds();
	}
})();

