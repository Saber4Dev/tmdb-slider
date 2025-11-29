<?php
/**
 * Frontend Class
 *
 * Handles shortcode registration and frontend rendering.
 *
 * @package TMDB_Slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TMDB_Slider_Front
 */
class TMDB_Slider_Front {

	/**
	 * Initialize frontend functionality
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		
		// Main flexible shortcodes
		add_shortcode( 'tmdb_hero_slider', array( $this, 'render_hero_slider' ) );
		add_shortcode( 'tmdb_popular_slider', array( $this, 'render_popular_slider' ) );
		add_shortcode( 'tmdb_top_rated_slider', array( $this, 'render_top_rated_slider' ) );
		add_shortcode( 'tmdb_now_playing_slider', array( $this, 'render_now_playing_slider' ) );
		
		// Backward compatibility - map old shortcodes to new system
		add_shortcode( 'tmdb_movie_hero_slider', array( $this, 'render_movie_hero_slider' ) );
		add_shortcode( 'tmdb_movie_popular_slider', array( $this, 'render_movie_popular_slider' ) );
		add_shortcode( 'tmdb_movie_top_rated_slider', array( $this, 'render_movie_top_rated_slider' ) );
		add_shortcode( 'tmdb_movie_now_playing_slider', array( $this, 'render_movie_now_playing_slider' ) );
		add_shortcode( 'tmdb_tv_hero_slider', array( $this, 'render_tv_hero_slider' ) );
		add_shortcode( 'tmdb_tv_popular_slider', array( $this, 'render_tv_popular_slider' ) );
		add_shortcode( 'tmdb_tv_top_rated_slider', array( $this, 'render_tv_top_rated_slider' ) );
		add_shortcode( 'tmdb_tv_on_air_slider', array( $this, 'render_tv_on_air_slider' ) );
	}

	/**
	 * Register REST API routes for background feature
	 */
	public function register_rest_routes() {
		register_rest_route(
			'tmdb-slider/v1',
			'/background/(?P<category>[a-zA-Z-]+)/(?P<type>[a-zA-Z]+)',
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_background_images' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get background images for REST API
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response data.
	 */
	public function get_background_images( $request ) {
		$category = $request->get_param( 'category' );
		$type = $request->get_param( 'type' );

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return new WP_Error( 'no_api_key', __( 'TMDb API key not configured.', 'tmdb-slider' ), array( 'status' => 400 ) );
		}

		$items = array();
		$limit = 10;

		// Map category to API method
		switch ( $category ) {
			case 'popular':
				if ( 'movie' === $type ) {
					$items = TMDB_Slider_API::get_popular_movies( $limit );
				} elseif ( 'tv' === $type ) {
					$items = TMDB_Slider_API::get_popular_tv_shows( $limit );
				}
				break;
			case 'trending':
				if ( 'movie' === $type ) {
					$items = TMDB_Slider_API::get_trending_movies( $limit );
				} elseif ( 'tv' === $type ) {
					$items = TMDB_Slider_API::get_trending_tv_shows( $limit );
				}
				break;
			case 'top-rated':
				if ( 'movie' === $type ) {
					$items = TMDB_Slider_API::get_top_rated_movies( $limit );
				} elseif ( 'tv' === $type ) {
					$items = TMDB_Slider_API::get_top_rated_tv_shows( $limit );
				}
				break;
			case 'now-playing':
				if ( 'movie' === $type ) {
					$items = TMDB_Slider_API::get_now_playing_movies( $limit );
				} else {
					return new WP_Error( 'invalid_type', __( 'Invalid type for now-playing category.', 'tmdb-slider' ), array( 'status' => 400 ) );
				}
				break;
			case 'on-air':
				if ( 'tv' === $type ) {
					$items = TMDB_Slider_API::get_on_air_tv_shows( $limit );
				} else {
					return new WP_Error( 'invalid_type', __( 'Invalid type for on-air category.', 'tmdb-slider' ), array( 'status' => 400 ) );
				}
				break;
			default:
				return new WP_Error( 'invalid_category', __( 'Invalid category.', 'tmdb-slider' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		if ( empty( $items ) ) {
			return new WP_Error( 'no_items', __( 'No items found.', 'tmdb-slider' ), array( 'status' => 404 ) );
		}

		// Filter items with backdrops
		$items_with_backdrops = array_filter( $items, function( $item ) {
			return ! empty( $item['backdrop_path'] );
		} );

		if ( empty( $items_with_backdrops ) ) {
			return new WP_Error( 'no_backdrops', __( 'No items with backdrops found.', 'tmdb-slider' ), array( 'status' => 404 ) );
		}

		// Extract backdrop URLs
		$backgrounds = array();
		foreach ( $items_with_backdrops as $item ) {
			$backgrounds[] = array(
				'url' => TMDB_Slider_API::get_image_url( $item['backdrop_path'], 'w1280' ),
				'title' => isset( $item['title'] ) ? $item['title'] : ( isset( $item['name'] ) ? $item['name'] : '' ),
			);
		}

		return rest_ensure_response( $backgrounds );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets() {
		// Always enqueue assets (for background feature that works via IDs)
		// Shortcodes are checked, but backgrounds work via ID detection in JS
		global $post;
		$has_shortcode = false;

		if ( is_a( $post, 'WP_Post' ) ) {
			$shortcodes = array(
				'tmdb_hero_slider',
				'tmdb_popular_slider',
				'tmdb_top_rated_slider',
				'tmdb_now_playing_slider',
				'tmdb_movie_hero_slider',
				'tmdb_movie_popular_slider',
				'tmdb_movie_top_rated_slider',
				'tmdb_movie_now_playing_slider',
				'tmdb_tv_hero_slider',
				'tmdb_tv_popular_slider',
				'tmdb_tv_top_rated_slider',
				'tmdb_tv_on_air_slider',
			);

			foreach ( $shortcodes as $shortcode ) {
				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					$has_shortcode = true;
					break;
				}
			}
		}

		// Always enqueue for background feature (works via ID detection in JS)
		if ( $has_shortcode || true ) {
			wp_enqueue_style(
				'tmdb-slider-css',
				TMDB_SLIDER_PLUGIN_URL . 'assets/css/tmdb-slider.css',
				array(),
				TMDB_SLIDER_VERSION
			);

			wp_enqueue_script(
				'tmdb-slider-js',
				TMDB_SLIDER_PLUGIN_URL . 'assets/js/tmdb-slider.js',
				array(),
				TMDB_SLIDER_VERSION,
				true
			);

			// Localize script with settings and REST API URL
			$settings = $this->get_slider_settings();
			wp_localize_script(
				'tmdb-slider-js',
				'tmdbSlider',
				array(
					'apiUrl' => rest_url( 'tmdb-slider/v1/background/' ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'bgSettings' => array(
						'position' => isset( $settings['bg_position'] ) ? $settings['bg_position'] : 'center',
						'size' => isset( $settings['bg_size'] ) ? $settings['bg_size'] : 'cover',
						'overlay' => isset( $settings['bg_overlay'] ) ? (int) $settings['bg_overlay'] : 0,
						'overlayColor' => isset( $settings['bg_overlay_color'] ) ? $settings['bg_overlay_color'] : '#000000',
						'changeInterval' => isset( $settings['bg_change_interval'] ) ? absint( $settings['bg_change_interval'] ) : 5,
					),
				)
			);
		}
	}

	/**
	 * Parse shortcode attributes with defaults
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $slider_type Slider type (hero, popular, etc.).
	 * @return array Parsed attributes.
	 */
	private function parse_attributes( $atts, $slider_type = 'hero' ) {
		$atts = shortcode_atts(
			array(
				'type' => '', // 'movie', 'tv', or empty for default
				'reverse' => '', // 'on', 'off', or empty for settings default
				'stop_on_hover' => '', // 'on', 'off', or empty for settings default
				'speed' => '', // Override speed (number in seconds)
				'poster_width' => '', // Override poster width (for row sliders)
			),
			$atts,
			'tmdb_' . $slider_type . '_slider'
		);

		// Normalize type
		$type = strtolower( trim( $atts['type'] ) );
		if ( ! in_array( $type, array( 'movie', 'tv' ), true ) ) {
			$type = ''; // Default behavior
		}

		// Normalize reverse
		$reverse = strtolower( trim( $atts['reverse'] ) );
		if ( 'on' === $reverse || 'yes' === $reverse || '1' === $reverse || 'true' === $reverse ) {
			$reverse = 1;
		} elseif ( 'off' === $reverse || 'no' === $reverse || '0' === $reverse || 'false' === $reverse ) {
			$reverse = 0;
		} else {
			$reverse = null; // Use settings default
		}

		// Normalize stop_on_hover
		$stop_on_hover = strtolower( trim( $atts['stop_on_hover'] ) );
		if ( 'on' === $stop_on_hover || 'yes' === $stop_on_hover || '1' === $stop_on_hover || 'true' === $stop_on_hover ) {
			$stop_on_hover = 1;
		} elseif ( 'off' === $stop_on_hover || 'no' === $stop_on_hover || '0' === $stop_on_hover || 'false' === $stop_on_hover ) {
			$stop_on_hover = 0;
		} else {
			$stop_on_hover = null; // Use settings default
		}

		// Parse speed
		$speed = ! empty( $atts['speed'] ) ? absint( $atts['speed'] ) : null;

		// Parse poster width
		$poster_width = ! empty( $atts['poster_width'] ) ? absint( $atts['poster_width'] ) : null;

		return array(
			'type' => $type,
			'reverse' => $reverse,
			'stop_on_hover' => $stop_on_hover,
			'speed' => $speed,
			'poster_width' => $poster_width,
		);
	}

	/**
	 * Get slider settings
	 *
	 * @return array Settings array.
	 */
	private function get_slider_settings() {
		$settings = get_option( 'tmdb_slider_settings', array() );
		return array(
			'row_slider_speed' => isset( $settings['row_slider_speed'] ) ? absint( $settings['row_slider_speed'] ) : 60,
			'hero_slider_speed' => isset( $settings['hero_slider_speed'] ) ? absint( $settings['hero_slider_speed'] ) : 50,
			'poster_width' => isset( $settings['poster_width'] ) ? absint( $settings['poster_width'] ) : 220,
			'show_play_icon' => isset( $settings['show_play_icon'] ) ? (int) $settings['show_play_icon'] : 1,
			'show_rating' => isset( $settings['show_rating'] ) ? (int) $settings['show_rating'] : 1,
			'show_names' => isset( $settings['show_names'] ) ? (int) $settings['show_names'] : 1,
			'name_color' => isset( $settings['name_color'] ) ? $settings['name_color'] : '#333333',
			'name_padding' => isset( $settings['name_padding'] ) ? $settings['name_padding'] : '0',
			'name_margin' => isset( $settings['name_margin'] ) ? $settings['name_margin'] : '10px 0 0 0',
			'name_text_align' => isset( $settings['name_text_align'] ) ? $settings['name_text_align'] : 'center',
			'name_font_size' => isset( $settings['name_font_size'] ) ? $settings['name_font_size'] : '14px',
			'poster_gap' => isset( $settings['poster_gap'] ) ? absint( $settings['poster_gap'] ) : 15,
			'make_poster_clickable' => isset( $settings['make_poster_clickable'] ) ? (int) $settings['make_poster_clickable'] : 1,
			'reverse_hero_slider' => isset( $settings['reverse_hero_slider'] ) ? (int) $settings['reverse_hero_slider'] : 0,
			'reverse_popular_slider' => isset( $settings['reverse_popular_slider'] ) ? (int) $settings['reverse_popular_slider'] : 0,
			'reverse_top_rated_slider' => isset( $settings['reverse_top_rated_slider'] ) ? (int) $settings['reverse_top_rated_slider'] : 0,
			'reverse_now_playing_slider' => isset( $settings['reverse_now_playing_slider'] ) ? (int) $settings['reverse_now_playing_slider'] : 0,
			'stop_on_hover_hero_slider' => isset( $settings['stop_on_hover_hero_slider'] ) ? (int) $settings['stop_on_hover_hero_slider'] : 1,
			'stop_on_hover_popular_slider' => isset( $settings['stop_on_hover_popular_slider'] ) ? (int) $settings['stop_on_hover_popular_slider'] : 1,
			'stop_on_hover_top_rated_slider' => isset( $settings['stop_on_hover_top_rated_slider'] ) ? (int) $settings['stop_on_hover_top_rated_slider'] : 1,
			'stop_on_hover_now_playing_slider' => isset( $settings['stop_on_hover_now_playing_slider'] ) ? (int) $settings['stop_on_hover_now_playing_slider'] : 1,
			'bg_position' => isset( $settings['bg_position'] ) ? $settings['bg_position'] : 'center',
			'bg_size' => isset( $settings['bg_size'] ) ? $settings['bg_size'] : 'cover',
			'bg_overlay' => isset( $settings['bg_overlay'] ) ? (int) $settings['bg_overlay'] : 0,
			'bg_overlay_color' => isset( $settings['bg_overlay_color'] ) ? $settings['bg_overlay_color'] : '#000000',
			'bg_change_interval' => isset( $settings['bg_change_interval'] ) ? absint( $settings['bg_change_interval'] ) : 5,
		);
	}

	/**
	 * Render hero slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_hero_slider( $atts ) {
		$parsed = $this->parse_attributes( $atts, 'hero' );

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		// Determine content type
		$type = $parsed['type'];
		if ( 'tv' === $type ) {
			$items = TMDB_Slider_API::get_trending_tv_shows( 10 );
		} elseif ( 'movie' === $type ) {
			$items = TMDB_Slider_API::get_trending_movies( 10 );
		} else {
			// Default: movies
			$items = TMDB_Slider_API::get_trending_movies( 10 );
		}

		if ( is_wp_error( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $items->get_error_message() ) . '</p>';
		}

		if ( empty( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No content found.', 'tmdb-slider' ) . '</p>';
		}

		$settings = $this->get_slider_settings();
		$speed = $parsed['speed'] !== null ? $parsed['speed'] : $settings['hero_slider_speed'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$make_clickable = $settings['make_poster_clickable'];
		$reverse = $parsed['reverse'] !== null ? $parsed['reverse'] : $settings['reverse_hero_slider'];
		$stop_on_hover = $parsed['stop_on_hover'] !== null ? $parsed['stop_on_hover'] : $settings['stop_on_hover_hero_slider'];
		$content_type = 'tv' === $type ? 'tv' : 'movie';

		// Filter items with backdrops
		$items_with_backdrops = array_filter( $items, function( $item ) {
			return ! empty( $item['backdrop_path'] );
		} );

		if ( empty( $items_with_backdrops ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No content with backdrops found.', 'tmdb-slider' ) . '</p>';
		}

		// Duplicate items for infinite scroll
		$all_items = array_merge( $items_with_backdrops, $items_with_backdrops );

		ob_start();
		?>
		<div class="tmdb-hero-slider" data-speed="<?php echo esc_attr( $speed ); ?>" data-reverse="<?php echo esc_attr( $reverse ); ?>" data-stop-on-hover="<?php echo esc_attr( $stop_on_hover ); ?>">
			<div class="tmdb-hero-slider-track">
				<?php foreach ( $all_items as $item ) : ?>
					<?php
					$backdrop_url = TMDB_Slider_API::get_image_url( $item['backdrop_path'], 'w1280' );
					$tmdb_url = TMDB_Slider_API::get_tmdb_url( $item['id'], $content_type );
					$rating = isset( $item['vote_average'] ) ? number_format( $item['vote_average'], 1 ) : '0.0';
					$title = isset( $item['title'] ) ? $item['title'] : ( isset( $item['name'] ) ? $item['name'] : '' );
					?>
					<div class="tmdb-hero-slide">
						<?php if ( $make_clickable ) : ?>
							<a href="<?php echo esc_url( $tmdb_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php endif; ?>
						<div class="tmdb-hero-backdrop" style="background-image: url('<?php echo esc_url( $backdrop_url ); ?>');"></div>
						<div class="tmdb-hero-overlay">
							<?php if ( $show_play_icon ) : ?>
								<div class="tmdb-play-icon">▶</div>
							<?php endif; ?>
							<?php if ( $show_rating ) : ?>
								<div class="tmdb-rating">⭐ <?php echo esc_html( $rating ); ?></div>
							<?php endif; ?>
						</div>
						<?php if ( $make_clickable ) : ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render popular slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_popular_slider( $atts ) {
		$parsed = $this->parse_attributes( $atts, 'popular' );

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		// Determine content type
		$type = $parsed['type'];
		if ( 'tv' === $type ) {
			$items = TMDB_Slider_API::get_popular_tv_shows( 20 );
			$content_type = 'tv';
		} elseif ( 'movie' === $type ) {
			$items = TMDB_Slider_API::get_popular_movies( 20 );
			$content_type = 'movie';
		} else {
			// Default: movies
			$items = TMDB_Slider_API::get_popular_movies( 20 );
			$content_type = 'movie';
		}

		if ( is_wp_error( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $items->get_error_message() ) . '</p>';
		}

		if ( empty( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No content found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $items, 'popular', $content_type, $parsed );
	}

	/**
	 * Render top rated slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_top_rated_slider( $atts ) {
		$parsed = $this->parse_attributes( $atts, 'top_rated' );

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		// Determine content type
		$type = $parsed['type'];
		if ( 'tv' === $type ) {
			$items = TMDB_Slider_API::get_top_rated_tv_shows( 20 );
			$content_type = 'tv';
		} elseif ( 'movie' === $type ) {
			$items = TMDB_Slider_API::get_top_rated_movies( 20 );
			$content_type = 'movie';
		} else {
			// Default: movies
			$items = TMDB_Slider_API::get_top_rated_movies( 20 );
			$content_type = 'movie';
		}

		if ( is_wp_error( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $items->get_error_message() ) . '</p>';
		}

		if ( empty( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No content found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $items, 'top-rated', $content_type, $parsed );
	}

	/**
	 * Render now playing slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_now_playing_slider( $atts ) {
		$parsed = $this->parse_attributes( $atts, 'now_playing' );

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		// Determine content type
		$type = $parsed['type'];
		if ( 'tv' === $type ) {
			$items = TMDB_Slider_API::get_on_air_tv_shows( 20 );
			$content_type = 'tv';
		} elseif ( 'movie' === $type ) {
			$items = TMDB_Slider_API::get_now_playing_movies( 20 );
			$content_type = 'movie';
		} else {
			// Default: movies
			$items = TMDB_Slider_API::get_now_playing_movies( 20 );
			$content_type = 'movie';
		}

		if ( is_wp_error( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $items->get_error_message() ) . '</p>';
		}

		if ( empty( $items ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No content found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $items, 'now-playing', $content_type, $parsed );
	}


	/**
	 * Backward compatibility: Movie hero slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_hero_slider( $atts ) {
		$atts['type'] = 'movie';
		return $this->render_hero_slider( $atts );
	}

	/**
	 * Backward compatibility: Movie popular slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_popular_slider( $atts ) {
		$atts['type'] = 'movie';
		return $this->render_popular_slider( $atts );
	}

	/**
	 * Backward compatibility: Movie top rated slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_top_rated_slider( $atts ) {
		$atts['type'] = 'movie';
		return $this->render_top_rated_slider( $atts );
	}

	/**
	 * Backward compatibility: Movie now playing slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_now_playing_slider( $atts ) {
		$atts['type'] = 'movie';
		return $this->render_now_playing_slider( $atts );
	}

	/**
	 * Backward compatibility: TV hero slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_hero_slider( $atts ) {
		$atts['type'] = 'tv';
		return $this->render_hero_slider( $atts );
	}

	/**
	 * Backward compatibility: TV popular slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_popular_slider( $atts ) {
		$atts['type'] = 'tv';
		return $this->render_popular_slider( $atts );
	}

	/**
	 * Backward compatibility: TV top rated slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_top_rated_slider( $atts ) {
		$atts['type'] = 'tv';
		return $this->render_top_rated_slider( $atts );
	}

	/**
	 * Backward compatibility: TV on air slider
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_on_air_slider( $atts ) {
		$atts['type'] = 'tv';
		return $this->render_now_playing_slider( $atts );
	}

	/**
	 * Render row slider (shared method for popular, top rated, now playing, sports)
	 *
	 * @param array  $items Array of movie/TV show data.
	 * @param string $slider_id Unique ID for this slider instance.
	 * @param string $type 'movie' or 'tv'.
	 * @param array  $parsed Parsed shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_row_slider( $items, $slider_id, $type = 'movie', $parsed = array() ) {
		$settings = $this->get_slider_settings();
		$speed = isset( $parsed['speed'] ) && $parsed['speed'] !== null ? $parsed['speed'] : $settings['row_slider_speed'];
		$poster_width = isset( $parsed['poster_width'] ) && $parsed['poster_width'] !== null ? $parsed['poster_width'] : $settings['poster_width'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$show_names = $settings['show_names'];
		$name_color = $settings['name_color'];
		$name_padding = $settings['name_padding'];
		$name_margin = $settings['name_margin'];
		$name_text_align = $settings['name_text_align'];
		$name_font_size = $settings['name_font_size'];
		$poster_gap = $settings['poster_gap'];
		$make_clickable = $settings['make_poster_clickable'];
		
		// Determine reverse and stop on hover
		$reverse = 0;
		$stop_on_hover = 1;
		
		// Use parsed attributes if provided, otherwise use settings
		if ( isset( $parsed['reverse'] ) && $parsed['reverse'] !== null ) {
			$reverse = $parsed['reverse'];
		} else {
			// Get from settings based on slider type
			if ( 'popular' === $slider_id ) {
				$reverse = $settings['reverse_popular_slider'];
				$stop_on_hover = $settings['stop_on_hover_popular_slider'];
			} elseif ( 'top-rated' === $slider_id ) {
				$reverse = $settings['reverse_top_rated_slider'];
				$stop_on_hover = $settings['stop_on_hover_top_rated_slider'];
			} elseif ( 'now-playing' === $slider_id ) {
				$reverse = $settings['reverse_now_playing_slider'];
				$stop_on_hover = $settings['stop_on_hover_now_playing_slider'];
			}
		}
		
		// Override stop_on_hover if provided in attributes
		if ( isset( $parsed['stop_on_hover'] ) && $parsed['stop_on_hover'] !== null ) {
			$stop_on_hover = $parsed['stop_on_hover'];
		}

		// Filter items with posters
		$items_with_posters = array_filter( $items, function( $item ) {
			return ! empty( $item['poster_path'] );
		} );

		if ( empty( $items_with_posters ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No items with posters found.', 'tmdb-slider' ) . '</p>';
		}

		// Duplicate items for infinite scroll
		$all_items = array_merge( $items_with_posters, $items_with_posters );

		ob_start();
		?>
		<div class="tmdb-row-slider" data-speed="<?php echo esc_attr( $speed ); ?>" data-poster-width="<?php echo esc_attr( $poster_width ); ?>" data-poster-gap="<?php echo esc_attr( $poster_gap ); ?>" data-reverse="<?php echo esc_attr( $reverse ); ?>" data-stop-on-hover="<?php echo esc_attr( $stop_on_hover ); ?>">
			<div class="tmdb-row-slider-track">
				<?php foreach ( $all_items as $item ) : ?>
					<?php
					$poster_url = TMDB_Slider_API::get_image_url( $item['poster_path'], 'w500' );
					$tmdb_url = TMDB_Slider_API::get_tmdb_url( $item['id'], $type );
					$rating = isset( $item['vote_average'] ) ? number_format( $item['vote_average'], 1 ) : '0.0';
					$title = isset( $item['title'] ) ? $item['title'] : ( isset( $item['name'] ) ? $item['name'] : '' );
					?>
					<div class="tmdb-row-slide">
						<?php if ( $make_clickable ) : ?>
							<a href="<?php echo esc_url( $tmdb_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php endif; ?>
						<div class="tmdb-poster-wrapper">
							<img src="<?php echo esc_url( $poster_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="tmdb-poster" />
							<div class="tmdb-poster-overlay">
								<?php if ( $show_play_icon ) : ?>
									<div class="tmdb-play-icon">▶</div>
								<?php endif; ?>
								<?php if ( $show_rating ) : ?>
									<div class="tmdb-rating">⭐ <?php echo esc_html( $rating ); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<?php if ( $make_clickable ) : ?>
							</a>
						<?php endif; ?>
						<?php if ( $show_names && ! empty( $title ) ) : ?>
							<?php
							$name_style = sprintf(
								'color: %s; padding: %s; margin: %s; text-align: %s; font-size: %s;',
								esc_attr( $name_color ),
								esc_attr( $name_padding ),
								esc_attr( $name_margin ),
								esc_attr( $name_text_align ),
								esc_attr( $name_font_size )
							);
							?>
							<div class="tmdb-poster-name" style="<?php echo $name_style; ?>"><?php echo esc_html( $title ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
