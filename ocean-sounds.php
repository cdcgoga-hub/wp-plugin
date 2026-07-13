<?php
/**
 * Plugin Name:       Ocean Sounds
 * Plugin URI:        https://github.com/cdcgoga-hub/wp-plugin
 * Description:       Plays a relaxing ocean/nature soundtrack when a visitor arrives on the site. Admins can upload/select multiple tracks; visitors can switch tracks and turn the sound on or off from a small, non-blocking floating widget.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            Ocean Sounds
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ocean-sounds
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

define( 'OCEAN_SOUNDS_VERSION', '1.0.0' );
define( 'OCEAN_SOUNDS_PLUGIN_FILE', __FILE__ );
define( 'OCEAN_SOUNDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OCEAN_SOUNDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OCEAN_SOUNDS_OPTION_KEY', 'ocean_sounds_options' );

require_once OCEAN_SOUNDS_PLUGIN_DIR . 'includes/class-ocean-sounds-settings.php';
require_once OCEAN_SOUNDS_PLUGIN_DIR . 'includes/class-ocean-sounds-admin.php';
require_once OCEAN_SOUNDS_PLUGIN_DIR . 'includes/class-ocean-sounds-frontend.php';

/**
 * Boot the plugin.
 */
function ocean_sounds_init() {
	load_plugin_textdomain( 'ocean-sounds', false, dirname( plugin_basename( OCEAN_SOUNDS_PLUGIN_FILE ) ) . '/languages' );

	Ocean_Sounds_Admin::instance();
	Ocean_Sounds_Frontend::instance();
}
add_action( 'plugins_loaded', 'ocean_sounds_init' );

/**
 * Set sane defaults on activation (only if no settings exist yet).
 */
function ocean_sounds_activate() {
	if ( false === get_option( OCEAN_SOUNDS_OPTION_KEY ) ) {
		update_option( OCEAN_SOUNDS_OPTION_KEY, Ocean_Sounds_Settings::get_defaults() );
	}
}
register_activation_hook( __FILE__, 'ocean_sounds_activate' );
