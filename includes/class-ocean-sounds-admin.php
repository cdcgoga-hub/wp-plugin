<?php
/**
 * Admin settings screen for Ocean Sounds.
 *
 * @package Ocean_Sounds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ocean_Sounds_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var Ocean_Sounds_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ocean_Sounds_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook everything up.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add the "Ocean Sounds" entry under Settings.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Ocean Sounds', 'ocean-sounds' ),
			__( 'Ocean Sounds', 'ocean-sounds' ),
			'manage_options',
			'ocean-sounds',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the option + sanitize callback.
	 */
	public function register_settings() {
		register_setting(
			'ocean_sounds_settings_group',
			OCEAN_SOUNDS_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Ocean_Sounds_Settings', 'sanitize' ),
				'default'           => Ocean_Sounds_Settings::get_defaults(),
			)
		);
	}

	/**
	 * Load the media uploader + our admin JS/CSS only on our settings page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_ocean-sounds' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'ocean-sounds-admin',
			OCEAN_SOUNDS_PLUGIN_URL . 'assets/css/ocean-sounds-admin.css',
			array(),
			OCEAN_SOUNDS_VERSION
		);

		wp_enqueue_script(
			'ocean-sounds-admin',
			OCEAN_SOUNDS_PLUGIN_URL . 'assets/js/ocean-sounds-admin.js',
			array( 'jquery' ),
			OCEAN_SOUNDS_VERSION,
			true
		);

		wp_localize_script(
			'ocean-sounds-admin',
			'OceanSoundsAdmin',
			array(
				'mediaTitle'  => __( 'Choose an audio file', 'ocean-sounds' ),
				'mediaButton' => __( 'Use this audio file', 'ocean-sounds' ),
				'untitled'    => __( 'Untitled track', 'ocean-sounds' ),
				'removeLabel' => __( 'Remove', 'ocean-sounds' ),
			)
		);
	}

	/**
	 * Render the settings page markup.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = Ocean_Sounds_Settings::get_options();
		?>
		<div class="wrap ocean-sounds-settings">
			<h1><?php esc_html_e( 'Ocean Sounds', 'ocean-sounds' ); ?></h1>
			<p><?php esc_html_e( 'Play a relaxing ocean or nature soundtrack for visitors when they arrive on your site. Visitors always get a small floating control to turn the sound on/off (and switch the track, if you add more than one).', 'ocean-sounds' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'ocean_sounds_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Ocean Sounds', 'ocean-sounds' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, $options['enabled'] ); ?> />
								<?php esc_html_e( 'Show the sound player on the site', 'ocean-sounds' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Play automatically', 'ocean-sounds' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[autoplay]" value="1" <?php checked( 1, $options['autoplay'] ); ?> />
								<?php esc_html_e( 'Try to start playing as soon as a visitor arrives (falls back to starting on the visitor’s first click/tap if the browser blocks autoplay)', 'ocean-sounds' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ocean-sounds-position"><?php esc_html_e( 'Widget position', 'ocean-sounds' ); ?></label></th>
						<td>
							<select id="ocean-sounds-position" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[position]">
								<?php
								$positions = array(
									'bottom-right' => __( 'Bottom right', 'ocean-sounds' ),
									'bottom-left'  => __( 'Bottom left', 'ocean-sounds' ),
									'top-right'    => __( 'Top right', 'ocean-sounds' ),
									'top-left'     => __( 'Top left', 'ocean-sounds' ),
								);
								foreach ( $positions as $value => $label ) {
									printf(
										'<option value="%1$s" %2$s>%3$s</option>',
										esc_attr( $value ),
										selected( $options['position'], $value, false ),
										esc_html( $label )
									);
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ocean-sounds-volume"><?php esc_html_e( 'Default volume', 'ocean-sounds' ); ?></label></th>
						<td>
							<input type="range" id="ocean-sounds-volume" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[volume]" min="0" max="100" value="<?php echo esc_attr( $options['volume'] ); ?>" />
							<span class="ocean-sounds-volume-value"><?php echo esc_html( $options['volume'] ); ?></span>%
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Melodies', 'ocean-sounds' ); ?></h2>
				<p><?php esc_html_e( 'Add one or more audio tracks (MP3/OGG) from your Media Library or by URL. If you add more than one, visitors will get a dropdown to switch between them.', 'ocean-sounds' ); ?></p>

				<table class="wp-list-table widefat fixed striped" id="ocean-sounds-tracks-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Default', 'ocean-sounds' ); ?></th>
							<th><?php esc_html_e( 'Name', 'ocean-sounds' ); ?></th>
							<th><?php esc_html_e( 'Audio file', 'ocean-sounds' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody id="ocean-sounds-tracks-body">
						<?php
						if ( ! empty( $options['tracks'] ) ) {
							foreach ( $options['tracks'] as $index => $track ) {
								$this->render_track_row( $index, $track, $options['default_track'] );
							}
						}
						?>
					</tbody>
				</table>

				<p>
					<button type="button" class="button" id="ocean-sounds-add-track"><?php esc_html_e( '+ Add track', 'ocean-sounds' ); ?></button>
				</p>

				<template id="ocean-sounds-track-row-template">
					<?php $this->render_track_row( '__INDEX__', array( 'name' => '', 'url' => '' ), -1 ); ?>
				</template>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render one row of the repeatable tracks table.
	 *
	 * @param int|string $index         Row index (or placeholder token for the JS template).
	 * @param array      $track         Track data (name, url).
	 * @param int        $default_track Index of the current default track.
	 */
	private function render_track_row( $index, $track, $default_track ) {
		$name = isset( $track['name'] ) ? $track['name'] : '';
		$url  = isset( $track['url'] ) ? $track['url'] : '';
		?>
		<tr class="ocean-sounds-track-row">
			<td>
				<input type="radio" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[default_track]" value="<?php echo esc_attr( $index ); ?>" <?php checked( (string) $default_track, (string) $index ); ?> />
			</td>
			<td>
				<input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Gentle Waves', 'ocean-sounds' ); ?>" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[tracks][<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $name ); ?>" />
			</td>
			<td>
				<input type="text" class="regular-text ocean-sounds-track-url" name="<?php echo esc_attr( OCEAN_SOUNDS_OPTION_KEY ); ?>[tracks][<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" />
				<button type="button" class="button ocean-sounds-choose-audio"><?php esc_html_e( 'Choose from Media Library', 'ocean-sounds' ); ?></button>
			</td>
			<td>
				<button type="button" class="button-link-delete ocean-sounds-remove-track"><?php esc_html_e( 'Remove', 'ocean-sounds' ); ?></button>
			</td>
		</tr>
		<?php
	}
}
