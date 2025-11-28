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
		// Global shortcodes (both movies and TV shows)
		add_shortcode( 'tmdb_hero_slider', array( $this, 'render_hero_slider' ) );
		add_shortcode( 'tmdb_popular_slider', array( $this, 'render_popular_slider' ) );
		add_shortcode( 'tmdb_top_rated_slider', array( $this, 'render_top_rated_slider' ) );
		add_shortcode( 'tmdb_now_playing_slider', array( $this, 'render_now_playing_slider' ) );
		add_shortcode( 'tmdb_sports_slider', array( $this, 'render_sports_slider' ) );
		// Movie-specific shortcodes
		add_shortcode( 'tmdb_movie_hero_slider', array( $this, 'render_movie_hero_slider' ) );
		add_shortcode( 'tmdb_movie_popular_slider', array( $this, 'render_movie_popular_slider' ) );
		add_shortcode( 'tmdb_movie_top_rated_slider', array( $this, 'render_movie_top_rated_slider' ) );
		add_shortcode( 'tmdb_movie_now_playing_slider', array( $this, 'render_movie_now_playing_slider' ) );
		// TV show-specific shortcodes
		add_shortcode( 'tmdb_tv_hero_slider', array( $this, 'render_tv_hero_slider' ) );
		add_shortcode( 'tmdb_tv_popular_slider', array( $this, 'render_tv_popular_slider' ) );
		add_shortcode( 'tmdb_tv_top_rated_slider', array( $this, 'render_tv_top_rated_slider' ) );
		add_shortcode( 'tmdb_tv_on_air_slider', array( $this, 'render_tv_on_air_slider' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets() {
		// Only enqueue if shortcodes are used on the page
		global $post;
		$has_shortcode = false;

		if ( is_a( $post, 'WP_Post' ) ) {
			$shortcodes = array(
				'tmdb_hero_slider',
				'tmdb_popular_slider',
				'tmdb_top_rated_slider',
				'tmdb_now_playing_slider',
				'tmdb_sports_slider',
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

		if ( $has_shortcode ) {
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
		}
	}

	/**
	 * Check if a slider is enabled
	 *
	 * @param string $slider_key Slider key.
	 * @return bool True if enabled.
	 */
	private function is_slider_enabled( $slider_key ) {
		$settings = get_option( 'tmdb_slider_settings', array() );
		return isset( $settings[ $slider_key ] ) && 1 === (int) $settings[ $slider_key ];
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
			'make_poster_clickable' => isset( $settings['make_poster_clickable'] ) ? (int) $settings['make_poster_clickable'] : 1,
		);
	}

	/**
	 * Render hero slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_hero_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_hero_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_trending_movies( 10 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		$settings = $this->get_slider_settings();
		$speed = $settings['hero_slider_speed'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$make_clickable = $settings['make_poster_clickable'];

		// Filter movies with backdrops
		$movies_with_backdrops = array_filter( $movies, function( $movie ) {
			return ! empty( $movie['backdrop_path'] );
		} );

		if ( empty( $movies_with_backdrops ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies with backdrops found.', 'tmdb-slider' ) . '</p>';
		}

		// Duplicate items for infinite scroll
		$all_movies = array_merge( $movies_with_backdrops, $movies_with_backdrops );

		ob_start();
		?>
		<div class="tmdb-hero-slider" data-speed="<?php echo esc_attr( $speed ); ?>">
			<div class="tmdb-hero-slider-track">
				<?php foreach ( $all_movies as $movie ) : ?>
					<?php
					$backdrop_url = TMDB_Slider_API::get_image_url( $movie['backdrop_path'], 'w1280' );
					$tmdb_url = TMDB_Slider_API::get_tmdb_url( $movie['id'], 'movie' );
					$rating = isset( $movie['vote_average'] ) ? number_format( $movie['vote_average'], 1 ) : '0.0';
					$title = isset( $movie['title'] ) ? $movie['title'] : ( isset( $movie['name'] ) ? $movie['name'] : '' );
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
		if ( ! $this->is_slider_enabled( 'enable_popular_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_popular_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'popular', 'movie' );
	}

	/**
	 * Render top rated slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_top_rated_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_top_rated_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_top_rated_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'top-rated', 'movie' );
	}

	/**
	 * Render now playing slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_now_playing_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_now_playing_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_now_playing_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'now-playing', 'movie' );
	}

	/**
	 * Render sports slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_sports_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_sports_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$settings = get_option( 'tmdb_slider_settings', array() );
		$sports_keywords = isset( $settings['sports_keywords'] ) ? trim( $settings['sports_keywords'] ) : '';

		if ( empty( $sports_keywords ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDB Slider: Please set Sports keyword IDs in settings for this slider.', 'tmdb-slider' ) . '</p>';
		}

		$shows = TMDB_Slider_API::get_sports_tv_shows( $sports_keywords, 20 );

		if ( is_wp_error( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $shows->get_error_message() ) . '</p>';
		}

		if ( empty( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $shows, 'sports', 'tv' );
	}

	/**
	 * Render movie hero slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_hero_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_hero_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_trending_movies( 10 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		$settings = $this->get_slider_settings();
		$speed = $settings['hero_slider_speed'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$make_clickable = $settings['make_poster_clickable'];

		// Filter movies with backdrops
		$movies_with_backdrops = array_filter( $movies, function( $movie ) {
			return ! empty( $movie['backdrop_path'] );
		} );

		if ( empty( $movies_with_backdrops ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies with backdrops found.', 'tmdb-slider' ) . '</p>';
		}

		// Duplicate items for infinite scroll
		$all_movies = array_merge( $movies_with_backdrops, $movies_with_backdrops );

		ob_start();
		?>
		<div class="tmdb-hero-slider" data-speed="<?php echo esc_attr( $speed ); ?>">
			<div class="tmdb-hero-slider-track">
				<?php foreach ( $all_movies as $movie ) : ?>
					<?php
					$backdrop_url = TMDB_Slider_API::get_image_url( $movie['backdrop_path'], 'w1280' );
					$tmdb_url = TMDB_Slider_API::get_tmdb_url( $movie['id'], 'movie' );
					$rating = isset( $movie['vote_average'] ) ? number_format( $movie['vote_average'], 1 ) : '0.0';
					$title = isset( $movie['title'] ) ? $movie['title'] : '';
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
	 * Render movie popular slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_popular_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_popular_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_popular_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'movie-popular', 'movie' );
	}

	/**
	 * Render movie top rated slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_top_rated_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_top_rated_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_top_rated_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'movie-top-rated', 'movie' );
	}

	/**
	 * Render movie now playing slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_movie_now_playing_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_now_playing_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$movies = TMDB_Slider_API::get_now_playing_movies( 20 );

		if ( is_wp_error( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $movies->get_error_message() ) . '</p>';
		}

		if ( empty( $movies ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No movies found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $movies, 'movie-now-playing', 'movie' );
	}

	/**
	 * Render TV hero slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_hero_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_hero_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$shows = TMDB_Slider_API::get_trending_tv_shows( 10 );

		if ( is_wp_error( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $shows->get_error_message() ) . '</p>';
		}

		if ( empty( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows found.', 'tmdb-slider' ) . '</p>';
		}

		$settings = $this->get_slider_settings();
		$speed = $settings['hero_slider_speed'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$make_clickable = $settings['make_poster_clickable'];

		// Filter shows with backdrops
		$shows_with_backdrops = array_filter( $shows, function( $show ) {
			return ! empty( $show['backdrop_path'] );
		} );

		if ( empty( $shows_with_backdrops ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows with backdrops found.', 'tmdb-slider' ) . '</p>';
		}

		// Duplicate items for infinite scroll
		$all_shows = array_merge( $shows_with_backdrops, $shows_with_backdrops );

		ob_start();
		?>
		<div class="tmdb-hero-slider" data-speed="<?php echo esc_attr( $speed ); ?>">
			<div class="tmdb-hero-slider-track">
				<?php foreach ( $all_shows as $show ) : ?>
					<?php
					$backdrop_url = TMDB_Slider_API::get_image_url( $show['backdrop_path'], 'w1280' );
					$tmdb_url = TMDB_Slider_API::get_tmdb_url( $show['id'], 'tv' );
					$rating = isset( $show['vote_average'] ) ? number_format( $show['vote_average'], 1 ) : '0.0';
					$title = isset( $show['name'] ) ? $show['name'] : '';
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
	 * Render TV popular slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_popular_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_popular_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$shows = TMDB_Slider_API::get_popular_tv_shows( 20 );

		if ( is_wp_error( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $shows->get_error_message() ) . '</p>';
		}

		if ( empty( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $shows, 'tv-popular', 'tv' );
	}

	/**
	 * Render TV top rated slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_top_rated_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_top_rated_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$shows = TMDB_Slider_API::get_top_rated_tv_shows( 20 );

		if ( is_wp_error( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $shows->get_error_message() ) . '</p>';
		}

		if ( empty( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $shows, 'tv-top-rated', 'tv' );
	}

	/**
	 * Render TV on air slider shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_tv_on_air_slider( $atts ) {
		if ( ! $this->is_slider_enabled( 'enable_now_playing_slider' ) ) {
			return '<p class="tmdb-slider-disabled">' . esc_html__( 'This slider is disabled in TMDB Slider settings.', 'tmdb-slider' ) . '</p>';
		}

		$api_key = TMDB_Slider_API::get_api_key();
		if ( ! $api_key ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'TMDb Slider: API key not configured.', 'tmdb-slider' ) . '</p>';
		}

		$shows = TMDB_Slider_API::get_on_air_tv_shows( 20 );

		if ( is_wp_error( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html( $shows->get_error_message() ) . '</p>';
		}

		if ( empty( $shows ) ) {
			return '<p class="tmdb-slider-error">' . esc_html__( 'No TV shows found.', 'tmdb-slider' ) . '</p>';
		}

		return $this->render_row_slider( $shows, 'tv-on-air', 'tv' );
	}

	/**
	 * Render row slider (shared method for popular, top rated, now playing, sports)
	 *
	 * @param array  $items Array of movie/TV show data.
	 * @param string $slider_id Unique ID for this slider instance.
	 * @param string $type 'movie' or 'tv'.
	 * @return string HTML output.
	 */
	private function render_row_slider( $items, $slider_id, $type = 'movie' ) {
		$settings = $this->get_slider_settings();
		$speed = $settings['row_slider_speed'];
		$poster_width = $settings['poster_width'];
		$show_play_icon = $settings['show_play_icon'];
		$show_rating = $settings['show_rating'];
		$show_names = $settings['show_names'];
		$make_clickable = $settings['make_poster_clickable'];

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
		<div class="tmdb-row-slider" data-speed="<?php echo esc_attr( $speed ); ?>" data-poster-width="<?php echo esc_attr( $poster_width ); ?>">
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
							<div class="tmdb-poster-name"><?php echo esc_html( $title ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

