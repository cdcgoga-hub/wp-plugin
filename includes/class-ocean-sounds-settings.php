<?php
/**
 * Shared settings helpers: defaults, sanitization, and accessors.
 *
 * @package Ocean_Sounds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ocean_Sounds_Settings {

	/**
	 * Default option values used on activation and as a fallback.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'enabled'       => 1,
			'autoplay'      => 1,
			'position'      => 'bottom-right',
			'volume'        => 50,
			'default_track' => 0,
			'tracks'        => array(),
		);
	}

	/**
	 * Get the merged (saved + defaults) settings array.
	 *
	 * @return array
	 */
	public static function get_options() {
		$saved = get_option( OCEAN_SOUNDS_OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Sanitize the settings form submission before it is saved.
	 *
	 * @param array $input Raw input from $_POST.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::get_defaults();
		$output   = array();

		$output['enabled']  = empty( $input['enabled'] ) ? 0 : 1;
		$output['autoplay'] = empty( $input['autoplay'] ) ? 0 : 1;

		$allowed_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
		$output['position'] = ( isset( $input['position'] ) && in_array( $input['position'], $allowed_positions, true ) )
			? $input['position']
			: $defaults['position'];

		$volume            = isset( $input['volume'] ) ? absint( $input['volume'] ) : $defaults['volume'];
		$output['volume']  = min( 100, max( 0, $volume ) );

		$tracks = array();
		if ( ! empty( $input['tracks'] ) && is_array( $input['tracks'] ) ) {
			foreach ( $input['tracks'] as $track ) {
				$name = isset( $track['name'] ) ? sanitize_text_field( wp_unslash( $track['name'] ) ) : '';
				$url  = isset( $track['url'] ) ? esc_url_raw( trim( wp_unslash( $track['url'] ) ) ) : '';

				if ( '' === $url ) {
					continue; // Skip empty rows.
				}

				if ( '' === $name ) {
					$name = __( 'Untitled track', 'ocean-sounds' );
				}

				$tracks[] = array(
					'name' => $name,
					'url'  => $url,
				);
			}
		}
		$output['tracks'] = array_values( $tracks );

		$default_track = isset( $input['default_track'] ) ? absint( $input['default_track'] ) : 0;
		if ( empty( $output['tracks'] ) ) {
			$default_track = 0;
		} else {
			$default_track = min( $default_track, count( $output['tracks'] ) - 1 );
		}
		$output['default_track'] = $default_track;

		return $output;
	}
}
