<?php
/**
 * Plugin Updater Class
 *
 * Handles automatic updates from GitHub repository.
 *
 * @package TMDB_Slider
 * @since 1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TMDB_Slider_Updater
 */
class TMDB_Slider_Updater {

	/**
	 * GitHub repository URL
	 *
	 * @var string
	 */
	private $github_url = 'https://github.com/Saber4Dev/tmdb-slider.git';

	/**
	 * GitHub API URL
	 *
	 * @var string
	 */
	private $api_url = 'https://api.github.com/repos/Saber4Dev/tmdb-slider/releases/latest';

	/**
	 * Plugin file
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Plugin basename
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * Constructor
	 *
	 * @param string $plugin_file Main plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->plugin_slug = 'tmdb-slider';
		$this->plugin_basename = plugin_basename( $plugin_file );

		// Hook into WordPress update system
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
	}

	/**
	 * Check for updates
	 *
	 * @param object $transient Update transient.
	 * @return object Modified transient.
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get latest release from GitHub
		$latest_release = $this->get_latest_release();

		if ( ! $latest_release || is_wp_error( $latest_release ) ) {
			return $transient;
		}

		$current_version = $this->get_current_version();
		$latest_version = $this->normalize_version( $latest_release['tag_name'] );

		// Compare versions
		if ( version_compare( $current_version, $latest_version, '<' ) ) {
			$package_url = $this->get_package_url( $latest_release );

			if ( $package_url ) {
				$obj = new stdClass();
				$obj->slug = $this->plugin_slug;
				$obj->plugin = $this->plugin_basename;
				$obj->new_version = $latest_version;
				$obj->url = $this->github_url;
				$obj->package = $package_url;
				$obj->requires = isset( $latest_release['requires'] ) ? $latest_release['requires'] : '';
				$obj->tested = isset( $latest_release['tested'] ) ? $latest_release['tested'] : '';
				$obj->last_updated = isset( $latest_release['published_at'] ) ? $latest_release['published_at'] : '';

				$transient->response[ $this->plugin_basename ] = $obj;
			}
		}

		return $transient;
	}

	/**
	 * Get plugin information for update screen
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested from the Plugin Installation API.
	 * @param object             $args Plugin API arguments.
	 * @return object|false Plugin information or false.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $this->plugin_slug !== $args->slug ) {
			return $result;
		}

		$latest_release = $this->get_latest_release();

		if ( ! $latest_release || is_wp_error( $latest_release ) ) {
			return $result;
		}

		$info = new stdClass();
		$info->name = 'TMDB Slider';
		$info->slug = $this->plugin_slug;
		$info->version = $this->normalize_version( $latest_release['tag_name'] );
		$info->author = 'Ranber';
		$info->author_profile = 'https://Ranber.com';
		$info->homepage = $this->github_url;
		$info->requires = isset( $latest_release['requires'] ) ? $latest_release['requires'] : '';
		$info->tested = isset( $latest_release['tested'] ) ? $latest_release['tested'] : '';
		$info->last_updated = isset( $latest_release['published_at'] ) ? $latest_release['published_at'] : '';
		$info->download_link = $this->get_package_url( $latest_release );
		$info->sections = array(
			'description' => isset( $latest_release['body'] ) ? $latest_release['body'] : '',
		);

		return $info;
	}

	/**
	 * Post install hook
	 *
	 * @param bool  $response Installation response.
	 * @param array $hook_extra Extra arguments.
	 * @param array $result Installation result.
	 * @return bool Response.
	 */
	public function post_install( $response, $hook_extra, $result ) {
		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
			return $response;
		}

		// Activate the plugin after update
		activate_plugin( $this->plugin_basename );

		return $response;
	}

	/**
	 * Get latest release from GitHub
	 *
	 * @return array|WP_Error Release data or error.
	 */
	private function get_latest_release() {
		$cache_key = 'tmdb_slider_latest_release';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			$this->api_url,
			array(
				'timeout' => 15,
				'sslverify' => true,
				'headers' => array(
					'Accept' => 'application/vnd.github.v3+json',
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			return new WP_Error( 'api_error', sprintf( __( 'GitHub API error (Status: %d)', 'tmdb-slider' ), $status_code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'json_error', __( 'Failed to parse GitHub API response.', 'tmdb-slider' ) );
		}

		// Cache for 12 hours
		set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Get package URL from release
	 *
	 * @param array $release Release data.
	 * @return string|false Package URL or false.
	 */
	private function get_package_url( $release ) {
		if ( ! isset( $release['zipball_url'] ) ) {
			return false;
		}

		// Add access token if needed (for private repos)
		$url = $release['zipball_url'];

		return $url;
	}

	/**
	 * Get current plugin version
	 *
	 * @return string Version.
	 */
	private function get_current_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = get_plugin_data( $this->plugin_file );
		return isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0';
	}

	/**
	 * Normalize version string
	 *
	 * @param string $version Version string.
	 * @return string Normalized version.
	 */
	private function normalize_version( $version ) {
		// Remove 'v' prefix if present
		$version = ltrim( $version, 'v' );
		return $version;
	}
}

