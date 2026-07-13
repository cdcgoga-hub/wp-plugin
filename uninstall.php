<?php
/**
 * Uninstall handler: remove plugin options when the plugin is deleted
 * (not just deactivated).
 *
 * @package Ocean_Sounds
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ocean_sounds_options' );
