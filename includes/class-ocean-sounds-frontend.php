<?php
/**
 * Front-end output: floating widget + audio player.
 *
 * @package Ocean_Sounds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ocean_Sounds_Frontend {

	/**
	 * Singleton instance.
	 *
	 * @var Ocean_Sounds_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ocean_Sounds_Frontend
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_widget' ) );
		add_filter( 'script_loader_tag', array( $this, 'defer_script' ), 10, 2 );
	}

	/**
	 * Force the `defer` attribute on our script tag so it never blocks
	 * HTML parsing, even on WP versions that don't support the
	 * `wp_script_add_data( 'strategy', 'defer' )` API (added in 6.3).
	 *
	 * @param string $tag    Script tag markup.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function defer_script( $tag, $handle ) {
		if ( 'ocean-sounds' !== $handle || false !== strpos( $tag, ' defer' ) ) {
			return $tag;
		}

		return str_replace( ' src=', ' defer src=', $tag );
	}

	/**
	 * Whether the player should output anything at all.
	 *
	 * @return bool
	 */
	private function is_active() {
		if ( is_admin() ) {
			return false;
		}

		$options = Ocean_Sounds_Settings::get_options();

		return ! empty( $options['enabled'] ) && ! empty( $options['tracks'] );
	}

	/**
	 * Enqueue the front-end CSS/JS (deferred, so it never blocks rendering).
	 */
	public function enqueue_assets() {
		if ( ! $this->is_active() ) {
			return;
		}

		$options = Ocean_Sounds_Settings::get_options();

		wp_enqueue_style(
			'ocean-sounds',
			OCEAN_SOUNDS_PLUGIN_URL . 'assets/css/ocean-sounds.css',
			array(),
			OCEAN_SOUNDS_VERSION
		);

		wp_enqueue_script(
			'ocean-sounds',
			OCEAN_SOUNDS_PLUGIN_URL . 'assets/js/ocean-sounds.js',
			array(),
			OCEAN_SOUNDS_VERSION,
			true
		);
		wp_script_add_data( 'ocean-sounds', 'strategy', 'defer' );

		$tracks = array();
		foreach ( $options['tracks'] as $track ) {
			$tracks[] = array(
				'name' => $track['name'],
				'url'  => $track['url'],
			);
		}

		wp_localize_script(
			'ocean-sounds',
			'OceanSoundsSettings',
			array(
				'tracks'       => $tracks,
				'defaultTrack' => (int) $options['default_track'],
				'volume'       => (int) $options['volume'],
				'autoplay'     => ! empty( $options['autoplay'] ),
				'i18n'         => array(
					'play'        => __( 'ხმის ჩართვა', 'ocean-sounds' ),
					'pause'       => __( 'ხმის გამორთვა', 'ocean-sounds' ),
					'trackLabel'  => __( 'Melody', 'ocean-sounds' ),
					'widgetLabel' => __( 'Ocean sounds player', 'ocean-sounds' ),
				),
			)
		);
	}

	/**
	 * Output the widget container. The JS fills it in; if JS is disabled,
	 * nothing renders and nothing is blocked.
	 */
	public function render_widget() {
		if ( ! $this->is_active() ) {
			return;
		}

		$options = Ocean_Sounds_Settings::get_options();
		$position = isset( $options['position'] ) ? $options['position'] : 'bottom-right';
		?>
		<div id="ocean-sounds-widget" class="ocean-sounds-widget ocean-sounds-pos-<?php echo esc_attr( $position ); ?>" aria-live="polite"></div>
		<?php
	}
}
