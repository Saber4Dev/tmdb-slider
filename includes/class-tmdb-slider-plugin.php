<?php
/**
 * Main Plugin Class
 *
 * @package TMDB_Slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TMDB_Slider_Plugin
 */
class TMDB_Slider_Plugin {

	/**
	 * Plugin instance
	 *
	 * @var TMDB_Slider_Plugin
	 */
	private static $instance = null;

	/**
	 * Admin instance
	 *
	 * @var TMDB_Slider_Admin
	 */
	public $admin;

	/**
	 * Frontend instance
	 *
	 * @var TMDB_Slider_Front
	 */
	public $front;

	/**
	 * Get plugin instance
	 *
	 * @return TMDB_Slider_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies() {
		require_once TMDB_SLIDER_PLUGIN_DIR . 'includes/class-tmdb-slider-api.php';
		require_once TMDB_SLIDER_PLUGIN_DIR . 'includes/class-tmdb-slider-admin.php';
		require_once TMDB_SLIDER_PLUGIN_DIR . 'includes/class-tmdb-slider-front.php';
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Load text domain
		load_plugin_textdomain(
			'tmdb-slider',
			false,
			dirname( plugin_basename( TMDB_SLIDER_PLUGIN_FILE ) ) . '/languages'
		);

		// Initialize admin
		if ( is_admin() ) {
			$this->admin = new TMDB_Slider_Admin();
			$this->admin->init();
		}

		// Initialize frontend
		$this->front = new TMDB_Slider_Front();
		$this->front->init();
	}

	/**
	 * Plugin activation
	 */
	public static function activate() {
		// Set default settings
		$default_settings = array(
			'api_key' => '',
			'row_slider_speed' => 60,
			'hero_slider_speed' => 50,
			'poster_width' => 220,
			'enable_hero_slider' => 1,
			'enable_popular_slider' => 1,
			'enable_top_rated_slider' => 1,
			'enable_now_playing_slider' => 1,
			'enable_sports_slider' => 1,
			'sports_keywords' => '',
			'show_play_icon' => 1,
			'show_rating' => 1,
			'show_names' => 1,
			'make_poster_clickable' => 1,
		);

		$existing_settings = get_option( 'tmdb_slider_settings', array() );
		$settings = wp_parse_args( $existing_settings, $default_settings );
		update_option( 'tmdb_slider_settings', $settings );
	}

	/**
	 * Plugin deactivation
	 */
	public static function deactivate() {
		// Clear transients
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_tmdb_' ) . '%'
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_timeout_tmdb_' ) . '%'
			)
		);
	}
}

