<?php
/**
 * Admin Settings Class
 *
 * Handles the WordPress admin settings page for TMDB Slider.
 *
 * @package TMDB_Slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TMDB_Slider_Admin
 */
class TMDB_Slider_Admin {

	/**
	 * Initialize admin functionality
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TMDB_SLIDER_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
	}

	/**
	 * Add settings page to WordPress admin
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'TMDB Slider Settings', 'tmdb-slider' ),
			__( 'TMDB Slider', 'tmdb-slider' ),
			'manage_options',
			'tmdb-slider',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting(
			'tmdb_slider_settings_group',
			'tmdb_slider_settings',
			array( $this, 'sanitize_settings' )
		);
	}

	/**
	 * Sanitize settings before saving
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['api_key'] ) ) {
			$sanitized['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		if ( isset( $input['row_slider_speed'] ) ) {
			$sanitized['row_slider_speed'] = absint( $input['row_slider_speed'] );
		}

		if ( isset( $input['hero_slider_speed'] ) ) {
			$sanitized['hero_slider_speed'] = absint( $input['hero_slider_speed'] );
		}

		if ( isset( $input['poster_width'] ) ) {
			$sanitized['poster_width'] = absint( $input['poster_width'] );
		}

		if ( isset( $input['enable_hero_slider'] ) ) {
			$sanitized['enable_hero_slider'] = 1;
		} else {
			$sanitized['enable_hero_slider'] = 0;
		}

		if ( isset( $input['enable_popular_slider'] ) ) {
			$sanitized['enable_popular_slider'] = 1;
		} else {
			$sanitized['enable_popular_slider'] = 0;
		}

		if ( isset( $input['enable_top_rated_slider'] ) ) {
			$sanitized['enable_top_rated_slider'] = 1;
		} else {
			$sanitized['enable_top_rated_slider'] = 0;
		}

		if ( isset( $input['enable_now_playing_slider'] ) ) {
			$sanitized['enable_now_playing_slider'] = 1;
		} else {
			$sanitized['enable_now_playing_slider'] = 0;
		}

		if ( isset( $input['enable_sports_slider'] ) ) {
			$sanitized['enable_sports_slider'] = 1;
		} else {
			$sanitized['enable_sports_slider'] = 0;
		}

		if ( isset( $input['sports_keywords'] ) ) {
			$sanitized['sports_keywords'] = sanitize_text_field( $input['sports_keywords'] );
		}

		if ( isset( $input['show_play_icon'] ) ) {
			$sanitized['show_play_icon'] = 1;
		} else {
			$sanitized['show_play_icon'] = 0;
		}

		if ( isset( $input['show_rating'] ) ) {
			$sanitized['show_rating'] = 1;
		} else {
			$sanitized['show_rating'] = 0;
		}

		if ( isset( $input['show_names'] ) ) {
			$sanitized['show_names'] = 1;
		} else {
			$sanitized['show_names'] = 0;
		}

		if ( isset( $input['make_poster_clickable'] ) ) {
			$sanitized['make_poster_clickable'] = 1;
		} else {
			$sanitized['make_poster_clickable'] = 0;
		}

		// Reverse direction options
		if ( isset( $input['reverse_hero_slider'] ) ) {
			$sanitized['reverse_hero_slider'] = 1;
		} else {
			$sanitized['reverse_hero_slider'] = 0;
		}

		if ( isset( $input['reverse_popular_slider'] ) ) {
			$sanitized['reverse_popular_slider'] = 1;
		} else {
			$sanitized['reverse_popular_slider'] = 0;
		}

		if ( isset( $input['reverse_top_rated_slider'] ) ) {
			$sanitized['reverse_top_rated_slider'] = 1;
		} else {
			$sanitized['reverse_top_rated_slider'] = 0;
		}

		if ( isset( $input['reverse_now_playing_slider'] ) ) {
			$sanitized['reverse_now_playing_slider'] = 1;
		} else {
			$sanitized['reverse_now_playing_slider'] = 0;
		}

		if ( isset( $input['reverse_sports_slider'] ) ) {
			$sanitized['reverse_sports_slider'] = 1;
		} else {
			$sanitized['reverse_sports_slider'] = 0;
		}

		// Stop on hover options
		if ( isset( $input['stop_on_hover_hero_slider'] ) ) {
			$sanitized['stop_on_hover_hero_slider'] = 1;
		} else {
			$sanitized['stop_on_hover_hero_slider'] = 0;
		}

		if ( isset( $input['stop_on_hover_popular_slider'] ) ) {
			$sanitized['stop_on_hover_popular_slider'] = 1;
		} else {
			$sanitized['stop_on_hover_popular_slider'] = 0;
		}

		if ( isset( $input['stop_on_hover_top_rated_slider'] ) ) {
			$sanitized['stop_on_hover_top_rated_slider'] = 1;
		} else {
			$sanitized['stop_on_hover_top_rated_slider'] = 0;
		}

		if ( isset( $input['stop_on_hover_now_playing_slider'] ) ) {
			$sanitized['stop_on_hover_now_playing_slider'] = 1;
		} else {
			$sanitized['stop_on_hover_now_playing_slider'] = 0;
		}

		if ( isset( $input['stop_on_hover_sports_slider'] ) ) {
			$sanitized['stop_on_hover_sports_slider'] = 1;
		} else {
			$sanitized['stop_on_hover_sports_slider'] = 0;
		}

		return $sanitized;
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'tmdb_slider_settings', array() );
		$api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		$row_slider_speed = isset( $settings['row_slider_speed'] ) ? $settings['row_slider_speed'] : 60;
		$hero_slider_speed = isset( $settings['hero_slider_speed'] ) ? $settings['hero_slider_speed'] : 50;
		$poster_width = isset( $settings['poster_width'] ) ? $settings['poster_width'] : 220;
		$enable_hero_slider = isset( $settings['enable_hero_slider'] ) ? $settings['enable_hero_slider'] : 1;
		$enable_popular_slider = isset( $settings['enable_popular_slider'] ) ? $settings['enable_popular_slider'] : 1;
		$enable_top_rated_slider = isset( $settings['enable_top_rated_slider'] ) ? $settings['enable_top_rated_slider'] : 1;
		$enable_now_playing_slider = isset( $settings['enable_now_playing_slider'] ) ? $settings['enable_now_playing_slider'] : 1;
		$enable_sports_slider = isset( $settings['enable_sports_slider'] ) ? $settings['enable_sports_slider'] : 1;
		$sports_keywords = isset( $settings['sports_keywords'] ) ? $settings['sports_keywords'] : '';
		$show_play_icon = isset( $settings['show_play_icon'] ) ? $settings['show_play_icon'] : 1;
		$show_rating = isset( $settings['show_rating'] ) ? $settings['show_rating'] : 1;
		$show_names = isset( $settings['show_names'] ) ? $settings['show_names'] : 1;
		$make_poster_clickable = isset( $settings['make_poster_clickable'] ) ? $settings['make_poster_clickable'] : 1;
		$reverse_hero_slider = isset( $settings['reverse_hero_slider'] ) ? $settings['reverse_hero_slider'] : 0;
		$reverse_popular_slider = isset( $settings['reverse_popular_slider'] ) ? $settings['reverse_popular_slider'] : 0;
		$reverse_top_rated_slider = isset( $settings['reverse_top_rated_slider'] ) ? $settings['reverse_top_rated_slider'] : 0;
		$reverse_now_playing_slider = isset( $settings['reverse_now_playing_slider'] ) ? $settings['reverse_now_playing_slider'] : 0;
		$reverse_sports_slider = isset( $settings['reverse_sports_slider'] ) ? $settings['reverse_sports_slider'] : 0;
		$stop_on_hover_hero_slider = isset( $settings['stop_on_hover_hero_slider'] ) ? $settings['stop_on_hover_hero_slider'] : 1;
		$stop_on_hover_popular_slider = isset( $settings['stop_on_hover_popular_slider'] ) ? $settings['stop_on_hover_popular_slider'] : 1;
		$stop_on_hover_top_rated_slider = isset( $settings['stop_on_hover_top_rated_slider'] ) ? $settings['stop_on_hover_top_rated_slider'] : 1;
		$stop_on_hover_now_playing_slider = isset( $settings['stop_on_hover_now_playing_slider'] ) ? $settings['stop_on_hover_now_playing_slider'] : 1;
		$stop_on_hover_sports_slider = isset( $settings['stop_on_hover_sports_slider'] ) ? $settings['stop_on_hover_sports_slider'] : 1;

		// Test API connection
		$api_status = $this->test_api_connection();

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'tmdb_slider_settings_group' );
				?>

				<h2 class="nav-tab-wrapper">
					<a href="#api-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'API Settings', 'tmdb-slider' ); ?></a>
					<a href="#slider-settings" class="nav-tab"><?php esc_html_e( 'Slider Settings', 'tmdb-slider' ); ?></a>
					<a href="#shortcodes" class="nav-tab"><?php esc_html_e( 'Shortcodes Reference', 'tmdb-slider' ); ?></a>
				</h2>

				<div id="api-settings" class="tab-content">
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="tmdb_api_key"><?php esc_html_e( 'TMDb API Key', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="text" id="tmdb_api_key" name="tmdb_slider_settings[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
								<p class="description">
									<?php esc_html_e( 'Enter your TMDb API key. You can obtain a free API key by creating an account at', 'tmdb-slider' ); ?>
									<a href="https://www.themoviedb.org/settings/api" target="_blank">themoviedb.org</a>.
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Connection Status', 'tmdb-slider' ); ?></th>
							<td>
								<?php
								if ( is_wp_error( $api_status ) ) {
									echo '<div class="notice notice-error inline"><p><strong>' . esc_html__( 'Connection Error:', 'tmdb-slider' ) . '</strong> ' . esc_html( $api_status->get_error_message() ) . '</p></div>';
								} elseif ( isset( $api_status['results'] ) ) {
									$count = count( $api_status['results'] );
									echo '<div class="notice notice-success inline"><p><strong>' . esc_html__( 'Connected to TMDb', 'tmdb-slider' ) . '</strong> (' . esc_html( $count ) . ' ' . esc_html__( 'results', 'tmdb-slider' ) . ')</p></div>';
								} else {
									echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Not Configured', 'tmdb-slider' ) . '</strong></p></div>';
								}
								?>
							</td>
						</tr>
					</table>
				</div>

				<div id="slider-settings" class="tab-content" style="display:none;">
					<h3><?php esc_html_e( 'Global Slider Settings', 'tmdb-slider' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="row_slider_speed"><?php esc_html_e( 'Row Slider Speed (seconds)', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="number" id="row_slider_speed" name="tmdb_slider_settings[row_slider_speed]" value="<?php echo esc_attr( $row_slider_speed ); ?>" min="1" step="1" />
								<p class="description"><?php esc_html_e( 'Animation duration for row sliders in seconds.', 'tmdb-slider' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="hero_slider_speed"><?php esc_html_e( 'Hero Slider Speed (seconds)', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="number" id="hero_slider_speed" name="tmdb_slider_settings[hero_slider_speed]" value="<?php echo esc_attr( $hero_slider_speed ); ?>" min="1" step="1" />
								<p class="description"><?php esc_html_e( 'Animation duration for hero slider in seconds.', 'tmdb-slider' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="poster_width"><?php esc_html_e( 'Poster Width (px)', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="number" id="poster_width" name="tmdb_slider_settings[poster_width]" value="<?php echo esc_attr( $poster_width ); ?>" min="100" step="10" />
								<p class="description"><?php esc_html_e( 'Width of poster images in row sliders.', 'tmdb-slider' ); ?></p>
							</td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Display Options', 'tmdb-slider' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Show Play Icon', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[show_play_icon]" value="1" <?php checked( $show_play_icon, 1 ); ?> />
									<?php esc_html_e( 'Show play icon on posters and hero slides', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Show Rating', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[show_rating]" value="1" <?php checked( $show_rating, 1 ); ?> />
									<?php esc_html_e( 'Show TMDB rating on posters and hero slides', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Show Names', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[show_names]" value="1" <?php checked( $show_names, 1 ); ?> />
									<?php esc_html_e( 'Show movie/TV show names under posters', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Make Poster Clickable', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[make_poster_clickable]" value="1" <?php checked( $make_poster_clickable, 1 ); ?> />
									<?php esc_html_e( 'Enable clickable links to TMDB pages', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Enable/Disable Sliders', 'tmdb-slider' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Hero Slider', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[enable_hero_slider]" value="1" <?php checked( $enable_hero_slider, 1 ); ?> />
									<?php esc_html_e( 'Enable [tmdb_hero_slider] shortcode', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[reverse_hero_slider]" value="1" <?php checked( $reverse_hero_slider, 1 ); ?> />
									<?php esc_html_e( 'Reverse direction', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[stop_on_hover_hero_slider]" value="1" <?php checked( $stop_on_hover_hero_slider, 1 ); ?> />
									<?php esc_html_e( 'Stop on hover', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Popular Slider', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[enable_popular_slider]" value="1" <?php checked( $enable_popular_slider, 1 ); ?> />
									<?php esc_html_e( 'Enable [tmdb_popular_slider] shortcode', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[reverse_popular_slider]" value="1" <?php checked( $reverse_popular_slider, 1 ); ?> />
									<?php esc_html_e( 'Reverse direction', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[stop_on_hover_popular_slider]" value="1" <?php checked( $stop_on_hover_popular_slider, 1 ); ?> />
									<?php esc_html_e( 'Stop on hover', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Top Rated Slider', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[enable_top_rated_slider]" value="1" <?php checked( $enable_top_rated_slider, 1 ); ?> />
									<?php esc_html_e( 'Enable [tmdb_top_rated_slider] shortcode', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[reverse_top_rated_slider]" value="1" <?php checked( $reverse_top_rated_slider, 1 ); ?> />
									<?php esc_html_e( 'Reverse direction', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[stop_on_hover_top_rated_slider]" value="1" <?php checked( $stop_on_hover_top_rated_slider, 1 ); ?> />
									<?php esc_html_e( 'Stop on hover', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Now Playing Slider', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[enable_now_playing_slider]" value="1" <?php checked( $enable_now_playing_slider, 1 ); ?> />
									<?php esc_html_e( 'Enable [tmdb_now_playing_slider] shortcode', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[reverse_now_playing_slider]" value="1" <?php checked( $reverse_now_playing_slider, 1 ); ?> />
									<?php esc_html_e( 'Reverse direction', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[stop_on_hover_now_playing_slider]" value="1" <?php checked( $stop_on_hover_now_playing_slider, 1 ); ?> />
									<?php esc_html_e( 'Stop on hover', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Sports Slider', 'tmdb-slider' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="tmdb_slider_settings[enable_sports_slider]" value="1" <?php checked( $enable_sports_slider, 1 ); ?> />
									<?php esc_html_e( 'Enable [tmdb_sports_slider] shortcode', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[reverse_sports_slider]" value="1" <?php checked( $reverse_sports_slider, 1 ); ?> />
									<?php esc_html_e( 'Reverse direction', 'tmdb-slider' ); ?>
								</label>
								<br />
								<label style="margin-top: 10px; display: inline-block;">
									<input type="checkbox" name="tmdb_slider_settings[stop_on_hover_sports_slider]" value="1" <?php checked( $stop_on_hover_sports_slider, 1 ); ?> />
									<?php esc_html_e( 'Stop on hover', 'tmdb-slider' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<h3><?php esc_html_e( 'Sports Slider Configuration', 'tmdb-slider' ); ?></h3>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="sports_keywords"><?php esc_html_e( 'Sports Keyword IDs (TMDb)', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="text" id="sports_keywords" name="tmdb_slider_settings[sports_keywords]" value="<?php echo esc_attr( $sports_keywords ); ?>" class="regular-text" />
								<p class="description">
									<?php esc_html_e( 'Comma-separated TMDb keyword IDs used by the sports slider (e.g., football, soccer, sports). You can find keyword IDs on TMDb by searching for keywords.', 'tmdb-slider' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div id="shortcodes" class="tab-content" style="display:none;">
					<h3><?php esc_html_e( 'Available Shortcodes', 'tmdb-slider' ); ?></h3>
					
					<h4><?php esc_html_e( 'Global Shortcodes (Movies & TV Shows)', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Status', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_hero_slider]</code></td>
								<td><?php esc_html_e( 'Backdrop hero slider (trending movies)', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_hero_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_popular_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_top_rated_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_now_playing_slider]</code></td>
								<td><?php esc_html_e( 'Now playing movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_now_playing_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_sports_slider]</code></td>
								<td><?php esc_html_e( 'Sports TV row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_sports_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>

					<h4><?php esc_html_e( 'Movie-Specific Shortcodes', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Status', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_movie_hero_slider]</code></td>
								<td><?php esc_html_e( 'Movie hero slider (trending movies)', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_hero_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_popular_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_top_rated_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_now_playing_slider]</code></td>
								<td><?php esc_html_e( 'Now playing movies row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_now_playing_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>

					<h4><?php esc_html_e( 'TV Show-Specific Shortcodes', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Status', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_tv_hero_slider]</code></td>
								<td><?php esc_html_e( 'TV show hero slider (trending TV shows)', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_hero_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular TV shows row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_popular_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated TV shows row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_top_rated_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_on_air_slider]</code></td>
								<td><?php esc_html_e( 'On air TV shows row', 'tmdb-slider' ); ?></td>
								<td>
									<?php
									if ( $enable_now_playing_slider ) {
										echo '<span style="color: green;">' . esc_html__( 'Enabled', 'tmdb-slider' ) . '</span>';
									} else {
										echo '<span style="color: red;">' . esc_html__( 'Disabled', 'tmdb-slider' ) . '</span>';
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<?php submit_button( __( 'Save Settings', 'tmdb-slider' ) ); ?>
			</form>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.nav-tab-wrapper a').on('click', function(e) {
				e.preventDefault();
				var target = $(this).attr('href');
				$('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				$('.tab-content').hide();
				$(target).show();
			});
		});
		</script>
		<?php
	}

	/**
	 * Test API connection
	 *
	 * @return array|WP_Error API response or error.
	 */
	private function test_api_connection() {
		$api_key = TMDB_Slider_API::get_api_key();
		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', __( 'API key is not configured.', 'tmdb-slider' ) );
		}

		return TMDB_Slider_API::test_connection();
	}

	/**
	 * Add action links to plugin row
	 *
	 * @param array $links Existing action links.
	 * @return array Modified action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=tmdb-slider' ),
			__( 'Settings', 'tmdb-slider' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}

