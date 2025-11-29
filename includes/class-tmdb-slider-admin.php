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

		if ( isset( $input['name_color'] ) ) {
			$sanitized['name_color'] = sanitize_hex_color( $input['name_color'] );
		}

		if ( isset( $input['name_padding'] ) ) {
			$sanitized['name_padding'] = sanitize_text_field( $input['name_padding'] );
		}

		if ( isset( $input['name_margin'] ) ) {
			$sanitized['name_margin'] = sanitize_text_field( $input['name_margin'] );
		}

		if ( isset( $input['name_text_align'] ) ) {
			$align = sanitize_text_field( $input['name_text_align'] );
			if ( in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
				$sanitized['name_text_align'] = $align;
			}
		}

		if ( isset( $input['name_font_size'] ) ) {
			$sanitized['name_font_size'] = sanitize_text_field( $input['name_font_size'] );
		}

		if ( isset( $input['poster_gap'] ) ) {
			$sanitized['poster_gap'] = absint( $input['poster_gap'] );
		}

		// Background settings
		if ( isset( $input['bg_position'] ) ) {
			$position = sanitize_text_field( $input['bg_position'] );
			if ( in_array( $position, array( 'center', 'top', 'bottom', 'left', 'right', 'top left', 'top right', 'bottom left', 'bottom right' ), true ) ) {
				$sanitized['bg_position'] = $position;
			}
		}

		if ( isset( $input['bg_size'] ) ) {
			$size = sanitize_text_field( $input['bg_size'] );
			if ( in_array( $size, array( 'cover', 'contain', 'auto', '100% 100%' ), true ) ) {
				$sanitized['bg_size'] = $size;
			}
		}

		if ( isset( $input['bg_overlay'] ) ) {
			$sanitized['bg_overlay'] = absint( $input['bg_overlay'] );
		} else {
			$sanitized['bg_overlay'] = 0;
		}

		if ( isset( $input['bg_overlay_color'] ) ) {
			$sanitized['bg_overlay_color'] = sanitize_hex_color( $input['bg_overlay_color'] );
		}

		if ( isset( $input['bg_change_interval'] ) ) {
			$sanitized['bg_change_interval'] = absint( $input['bg_change_interval'] );
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
		$show_play_icon = isset( $settings['show_play_icon'] ) ? $settings['show_play_icon'] : 1;
		$show_rating = isset( $settings['show_rating'] ) ? $settings['show_rating'] : 1;
		$show_names = isset( $settings['show_names'] ) ? $settings['show_names'] : 1;
		$name_color = isset( $settings['name_color'] ) ? $settings['name_color'] : '#333333';
		$name_padding = isset( $settings['name_padding'] ) ? $settings['name_padding'] : '0';
		$name_margin = isset( $settings['name_margin'] ) ? $settings['name_margin'] : '10px 0 0 0';
		$name_text_align = isset( $settings['name_text_align'] ) ? $settings['name_text_align'] : 'center';
		$name_font_size = isset( $settings['name_font_size'] ) ? $settings['name_font_size'] : '14px';
		$poster_gap = isset( $settings['poster_gap'] ) ? $settings['poster_gap'] : 15;
		$make_poster_clickable = isset( $settings['make_poster_clickable'] ) ? $settings['make_poster_clickable'] : 1;
		$bg_position = isset( $settings['bg_position'] ) ? $settings['bg_position'] : 'center';
		$bg_size = isset( $settings['bg_size'] ) ? $settings['bg_size'] : 'cover';
		$bg_overlay = isset( $settings['bg_overlay'] ) ? $settings['bg_overlay'] : 0;
		$bg_overlay_color = isset( $settings['bg_overlay_color'] ) ? $settings['bg_overlay_color'] : '#000000';
		$bg_change_interval = isset( $settings['bg_change_interval'] ) ? $settings['bg_change_interval'] : 5;

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
					<a href="#shortcodes" class="nav-tab"><?php esc_html_e( 'Help', 'tmdb-slider' ); ?></a>
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
								<br />
								<div style="margin-top: 15px;">
									<label for="name_color" style="display: inline-block; margin-right: 15px;">
										<?php esc_html_e( 'Text Color:', 'tmdb-slider' ); ?>
										<input type="color" id="name_color" name="tmdb_slider_settings[name_color]" value="<?php echo esc_attr( $name_color ); ?>" style="margin-left: 5px;" />
									</label>
									<label for="name_font_size" style="display: inline-block; margin-right: 15px;">
										<?php esc_html_e( 'Font Size:', 'tmdb-slider' ); ?>
										<input type="text" id="name_font_size" name="tmdb_slider_settings[name_font_size]" value="<?php echo esc_attr( $name_font_size ); ?>" style="width: 80px; margin-left: 5px;" placeholder="14px" />
									</label>
									<label for="name_text_align" style="display: inline-block;">
										<?php esc_html_e( 'Text Align:', 'tmdb-slider' ); ?>
										<select id="name_text_align" name="tmdb_slider_settings[name_text_align]" style="margin-left: 5px;">
											<option value="left" <?php selected( $name_text_align, 'left' ); ?>><?php esc_html_e( 'Left', 'tmdb-slider' ); ?></option>
											<option value="center" <?php selected( $name_text_align, 'center' ); ?>><?php esc_html_e( 'Center', 'tmdb-slider' ); ?></option>
											<option value="right" <?php selected( $name_text_align, 'right' ); ?>><?php esc_html_e( 'Right', 'tmdb-slider' ); ?></option>
										</select>
									</label>
								</div>
								<div style="margin-top: 10px;">
									<label for="name_padding" style="display: inline-block; margin-right: 15px;">
										<?php esc_html_e( 'Padding:', 'tmdb-slider' ); ?>
										<input type="text" id="name_padding" name="tmdb_slider_settings[name_padding]" value="<?php echo esc_attr( $name_padding ); ?>" style="width: 100px; margin-left: 5px;" placeholder="0" />
										<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'e.g., 5px or 5px 10px', 'tmdb-slider' ); ?></span>
									</label>
									<label for="name_margin" style="display: inline-block;">
										<?php esc_html_e( 'Margin:', 'tmdb-slider' ); ?>
										<input type="text" id="name_margin" name="tmdb_slider_settings[name_margin]" value="<?php echo esc_attr( $name_margin ); ?>" style="width: 100px; margin-left: 5px;" placeholder="10px 0 0 0" />
										<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'e.g., 10px 0 0 0', 'tmdb-slider' ); ?></span>
									</label>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="poster_gap"><?php esc_html_e( 'Poster Gap (px)', 'tmdb-slider' ); ?></label>
							</th>
							<td>
								<input type="number" id="poster_gap" name="tmdb_slider_settings[poster_gap]" value="<?php echo esc_attr( $poster_gap ); ?>" min="0" step="1" />
								<p class="description"><?php esc_html_e( 'Gap between posters in row sliders (in pixels).', 'tmdb-slider' ); ?></p>
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

				</div>

				<div id="shortcodes" class="tab-content" style="display:none;">
					<h3><?php esc_html_e( 'How to Use TMDB Slider', 'tmdb-slider' ); ?></h3>
					
					<h4><?php esc_html_e( '🎬 Dynamic Background Feature', 'tmdb-slider' ); ?></h4>
					<p><?php esc_html_e( 'Automatically apply TMDb movie/TV backgrounds to any div by adding a specific ID. The backgrounds will cycle through automatically!', 'tmdb-slider' ); ?></p>
					
					<p><strong><?php esc_html_e( 'Step 1:', 'tmdb-slider' ); ?></strong> <?php esc_html_e( 'Choose a background ID from the list below:', 'tmdb-slider' ); ?></p>
					<table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Background ID', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Content Type', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>#tmdb--popular-movie-background</code></td>
								<td><?php esc_html_e( 'Popular movies', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--popular-tv-background</code></td>
								<td><?php esc_html_e( 'Popular TV shows', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--trending-movie-background</code></td>
								<td><?php esc_html_e( 'Trending movies', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--trending-tv-background</code></td>
								<td><?php esc_html_e( 'Trending TV shows', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--top-rated-movie-background</code></td>
								<td><?php esc_html_e( 'Top rated movies', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--top-rated-tv-background</code></td>
								<td><?php esc_html_e( 'Top rated TV shows', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--now-playing-movie-background</code></td>
								<td><?php esc_html_e( 'Now playing movies', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>#tmdb--on-air-tv-background</code></td>
								<td><?php esc_html_e( 'On air TV shows', 'tmdb-slider' ); ?></td>
							</tr>
						</tbody>
					</table>
					
					<p><strong><?php esc_html_e( 'Step 2:', 'tmdb-slider' ); ?></strong> <?php esc_html_e( 'Add the ID to your div element:', 'tmdb-slider' ); ?></p>
					<pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;"><code>&lt;div id="tmdb--popular-movie-background" style="width: 100%; height: 400px; padding: 20px;"&gt;
    &lt;h2 style="position: relative; z-index: 2; color: white;"&gt;Your Content Here&lt;/h2&gt;
&lt;/div&gt;</code></pre>
					
					<p><strong><?php esc_html_e( 'That\'s it!', 'tmdb-slider' ); ?></strong> <?php esc_html_e( 'The plugin will automatically fetch and apply TMDb backgrounds that cycle through. Configure background settings (position, size, overlay) in the Slider Settings tab above.', 'tmdb-slider' ); ?></p>
					
					<hr style="margin: 30px 0;" />
					
					<h4><?php esc_html_e( '📝 Shortcodes', 'tmdb-slider' ); ?></h4>
					<p><?php esc_html_e( 'Use shortcodes to display sliders anywhere on your site. All shortcodes support flexible attributes:', 'tmdb-slider' ); ?></p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li><code>type=movie</code> <?php esc_html_e( 'or', 'tmdb-slider' ); ?> <code>type=tv</code> - <?php esc_html_e( 'Filter by content type', 'tmdb-slider' ); ?></li>
						<li><code>reverse=on</code> <?php esc_html_e( 'or', 'tmdb-slider' ); ?> <code>reverse=off</code> - <?php esc_html_e( 'Control animation direction', 'tmdb-slider' ); ?></li>
						<li><code>stop_on_hover=on</code> <?php esc_html_e( 'or', 'tmdb-slider' ); ?> <code>stop_on_hover=off</code> - <?php esc_html_e( 'Control pause on hover', 'tmdb-slider' ); ?></li>
						<li><code>speed=50</code> - <?php esc_html_e( 'Override animation speed (seconds)', 'tmdb-slider' ); ?></li>
						<li><code>poster_width=220</code> - <?php esc_html_e( 'Override poster width for row sliders (pixels)', 'tmdb-slider' ); ?></li>
					</ul>
					
					<p><strong><?php esc_html_e( 'Shortcode Examples:', 'tmdb-slider' ); ?></strong></p>
					<div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
						<p><strong><?php esc_html_e( 'Basic Usage:', 'tmdb-slider' ); ?></strong></p>
						<ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
							<li><code>[tmdb_hero_slider]</code> - <?php esc_html_e( 'Default hero slider with trending movies', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_popular_slider]</code> - <?php esc_html_e( 'Popular movies row slider', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_top_rated_slider]</code> - <?php esc_html_e( 'Top rated movies row slider', 'tmdb-slider' ); ?></li>
						</ul>
						
						<p><strong><?php esc_html_e( 'With Type Filter:', 'tmdb-slider' ); ?></strong></p>
						<ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
							<li><code>[tmdb_hero_slider type=movie]</code> - <?php esc_html_e( 'Movies only', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_hero_slider type=tv]</code> - <?php esc_html_e( 'TV shows only', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_popular_slider type=tv]</code> - <?php esc_html_e( 'Popular TV shows', 'tmdb-slider' ); ?></li>
						</ul>
						
						<p><strong><?php esc_html_e( 'With Animation Control:', 'tmdb-slider' ); ?></strong></p>
						<ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
							<li><code>[tmdb_hero_slider reverse=on]</code> - <?php esc_html_e( 'Reverse animation direction', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_popular_slider reverse=on stop_on_hover=off]</code> - <?php esc_html_e( 'Reverse + no pause on hover', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_hero_slider speed=40]</code> - <?php esc_html_e( 'Custom animation speed (40 seconds)', 'tmdb-slider' ); ?></li>
						</ul>
						
						<p><strong><?php esc_html_e( 'Advanced Examples:', 'tmdb-slider' ); ?></strong></p>
						<ul style="list-style: disc; margin-left: 20px;">
							<li><code>[tmdb_hero_slider type=movie reverse=on]</code> - <?php esc_html_e( 'Movies with reverse direction', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_popular_slider type=tv speed=40]</code> - <?php esc_html_e( 'TV shows with custom speed', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_top_rated_slider type=movie reverse=on poster_width=250]</code> - <?php esc_html_e( 'Movies, reverse, custom poster width', 'tmdb-slider' ); ?></li>
							<li><code>[tmdb_now_playing_slider type=tv reverse=on speed=30]</code> - <?php esc_html_e( 'TV shows, reverse, fast speed', 'tmdb-slider' ); ?></li>
						</ul>
					</div>
					
					<p><strong><?php esc_html_e( 'Where to Use:', 'tmdb-slider' ); ?></strong></p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li><?php esc_html_e( 'In Posts/Pages: Just paste the shortcode directly', 'tmdb-slider' ); ?></li>
						<li><?php esc_html_e( 'In Elementor: Add a Shortcode widget and paste the shortcode', 'tmdb-slider' ); ?></li>
						<li><?php esc_html_e( 'In PHP Templates: Use', 'tmdb-slider' ); ?> <code>&lt;?php echo do_shortcode('[tmdb_hero_slider]'); ?&gt;</code></li>
					</ul>
					
					<h4><?php esc_html_e( 'Main Shortcodes', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_hero_slider]</code></td>
								<td><?php esc_html_e( 'Backdrop hero slider (trending content)', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular content row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated content row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_now_playing_slider]</code></td>
								<td><?php esc_html_e( 'Now playing/on air content row', 'tmdb-slider' ); ?></td>
							</tr>
						</tbody>
					</table>

					<h4><?php esc_html_e( 'Movie-Specific Shortcodes (Backward Compatibility)', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_movie_hero_slider]</code></td>
								<td><?php esc_html_e( 'Movie hero slider (trending movies)', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular movies row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated movies row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_movie_now_playing_slider]</code></td>
								<td><?php esc_html_e( 'Now playing movies row', 'tmdb-slider' ); ?></td>
							</tr>
						</tbody>
					</table>

					<h4><?php esc_html_e( 'TV Show-Specific Shortcodes (Backward Compatibility)', 'tmdb-slider' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Shortcode', 'tmdb-slider' ); ?></th>
								<th><?php esc_html_e( 'Description', 'tmdb-slider' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>[tmdb_tv_hero_slider]</code></td>
								<td><?php esc_html_e( 'TV show hero slider (trending TV shows)', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_popular_slider]</code></td>
								<td><?php esc_html_e( 'Popular TV shows row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_top_rated_slider]</code></td>
								<td><?php esc_html_e( 'Top rated TV shows row', 'tmdb-slider' ); ?></td>
							</tr>
							<tr>
								<td><code>[tmdb_tv_on_air_slider]</code></td>
								<td><?php esc_html_e( 'On air TV shows row', 'tmdb-slider' ); ?></td>
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

