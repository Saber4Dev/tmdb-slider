<?php
/**
 * TMDb API Client Class
 *
 * Handles all interactions with The Movie Database API.
 *
 * @package TMDB_Slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TMDB_Slider_API
 */
class TMDB_Slider_API {

	/**
	 * Base URL for TMDb API
	 *
	 * @var string
	 */
	const BASE_URL = 'https://api.themoviedb.org/3/';

	/**
	 * Get API key from settings
	 *
	 * @return string|false API key or false if not set
	 */
	public static function get_api_key() {
		$settings = get_option( 'tmdb_slider_settings', array() );
		return isset( $settings['api_key'] ) ? trim( $settings['api_key'] ) : false;
	}

	/**
	 * Build TMDb API URL
	 *
	 * @param string $endpoint API endpoint (e.g., 'movie/popular').
	 * @param array  $params Additional query parameters.
	 * @return string|false Full API URL or false if API key is missing.
	 */
	public static function build_url( $endpoint, $params = array() ) {
		$api_key = self::get_api_key();

		if ( ! $api_key ) {
			return false;
		}

		$default_params = array(
			'api_key' => $api_key,
			'language' => 'en-US',
		);

		$params = array_merge( $default_params, $params );
		$url = self::BASE_URL . $endpoint . '?' . http_build_query( $params );

		return $url;
	}

	/**
	 * Fetch data from TMDb API
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $params Additional query parameters.
	 * @param int    $cache_duration Cache duration in seconds (default: 3600 = 1 hour).
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public static function fetch( $endpoint, $params = array(), $cache_duration = 3600 ) {
		$url = self::build_url( $endpoint, $params );

		if ( ! $url ) {
			return new WP_Error( 'no_api_key', __( 'TMDb API key is not configured.', 'tmdb-slider' ) );
		}

		// Check cache
		$cache_key = 'tmdb_' . md5( $url );
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Make API request
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error( 'api_error', sprintf( __( 'TMDb API error (Status: %d): %s', 'tmdb-slider' ), $status_code, $body ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'json_error', __( 'Failed to parse TMDb API response.', 'tmdb-slider' ) );
		}

		// Cache the response
		if ( ! is_wp_error( $data ) && isset( $data['results'] ) ) {
			set_transient( $cache_key, $data, $cache_duration );
		}

		return $data;
	}

	/**
	 * Test API connection
	 *
	 * @return array|WP_Error Response data or WP_Error on failure.
	 */
	public static function test_connection() {
		return self::fetch( 'movie/popular', array(), 0 ); // No cache for test
	}

	/**
	 * Get trending movies for the week
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of movie data or WP_Error.
	 */
	public static function get_trending_movies( $limit = 10 ) {
		$response = self::fetch( 'trending/movie/week' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No trending movies found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get popular movies
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of movie data or WP_Error.
	 */
	public static function get_popular_movies( $limit = 20 ) {
		$response = self::fetch( 'movie/popular' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No popular movies found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get top rated movies
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of movie data or WP_Error.
	 */
	public static function get_top_rated_movies( $limit = 20 ) {
		$response = self::fetch( 'movie/top_rated' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No top rated movies found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get now playing movies
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of movie data or WP_Error.
	 */
	public static function get_now_playing_movies( $limit = 20 ) {
		$response = self::fetch( 'movie/now_playing' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No now playing movies found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get sports TV shows
	 *
	 * @param string $keywords Comma-separated keyword IDs.
	 * @param int    $limit Maximum number of results.
	 * @return array|WP_Error Array of TV show data or WP_Error.
	 */
	public static function get_sports_tv_shows( $keywords, $limit = 20 ) {
		if ( empty( $keywords ) ) {
			return new WP_Error( 'no_keywords', __( 'Sports keyword IDs are not configured.', 'tmdb-slider' ) );
		}

		$keyword_ids = array_map( 'trim', explode( ',', $keywords ) );
		$keyword_ids = array_filter( $keyword_ids );

		if ( empty( $keyword_ids ) ) {
			return new WP_Error( 'no_keywords', __( 'Sports keyword IDs are not configured.', 'tmdb-slider' ) );
		}

		$params = array(
			'sort_by' => 'popularity.desc',
			'with_keywords' => implode( ',', $keyword_ids ),
		);

		$response = self::fetch( 'discover/tv', $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No sports TV shows found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get trending TV shows for the week
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of TV show data or WP_Error.
	 */
	public static function get_trending_tv_shows( $limit = 10 ) {
		$response = self::fetch( 'trending/tv/week' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No trending TV shows found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get popular TV shows
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of TV show data or WP_Error.
	 */
	public static function get_popular_tv_shows( $limit = 20 ) {
		$response = self::fetch( 'tv/popular' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No popular TV shows found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get top rated TV shows
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of TV show data or WP_Error.
	 */
	public static function get_top_rated_tv_shows( $limit = 20 ) {
		$response = self::fetch( 'tv/top_rated' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No top rated TV shows found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get on air TV shows (currently airing)
	 *
	 * @param int $limit Maximum number of results.
	 * @return array|WP_Error Array of TV show data or WP_Error.
	 */
	public static function get_on_air_tv_shows( $limit = 20 ) {
		$response = self::fetch( 'tv/on_the_air' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['results'] ) ) {
			return new WP_Error( 'no_results', __( 'No on air TV shows found.', 'tmdb-slider' ) );
		}

		$results = array_slice( $response['results'], 0, $limit );
		return $results;
	}

	/**
	 * Get image URL
	 *
	 * @param string $path Image path from TMDb.
	 * @param string $size Image size (e.g., 'w500', 'w1280').
	 * @return string Full image URL.
	 */
	public static function get_image_url( $path, $size = 'w500' ) {
		if ( empty( $path ) ) {
			return '';
		}

		return 'https://image.tmdb.org/t/p/' . $size . $path;
	}

	/**
	 * Get TMDb URL for a movie or TV show
	 *
	 * @param int    $id Item ID.
	 * @param string $type 'movie' or 'tv'.
	 * @return string TMDb URL.
	 */
	public static function get_tmdb_url( $id, $type = 'movie' ) {
		return 'https://www.themoviedb.org/' . $type . '/' . $id;
	}
}

