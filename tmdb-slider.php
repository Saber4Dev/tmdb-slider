<?php
/**
 * Plugin Name: TMDB Slider
 * Plugin URI: https://github.com/Saber4Dev/tmdb-slider
 * Description: A WordPress plugin that integrates with The Movie Database (TMDb) API and provides multiple Elementor-friendly sliders using shortcodes.
 * Version: 1.0.1
 * Author: Ranber
 * Author URI: https://Ranber.com
 * Text Domain: tmdb-slider
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'TMDB_SLIDER_VERSION', '1.0.1' );
define( 'TMDB_SLIDER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TMDB_SLIDER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TMDB_SLIDER_PLUGIN_FILE', __FILE__ );

// Load the main plugin class
require_once TMDB_SLIDER_PLUGIN_DIR . 'includes/class-tmdb-slider-plugin.php';

// Initialize the plugin
function tmdb_slider_init() {
	$plugin = TMDB_Slider_Plugin::get_instance();
	$plugin->init();
}
add_action( 'plugins_loaded', 'tmdb_slider_init' );

// Activation hook
register_activation_hook( __FILE__, array( 'TMDB_Slider_Plugin', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'TMDB_Slider_Plugin', 'deactivate' ) );

