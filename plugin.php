<?php

/*
 * Plugin Name: Large Network Management
 * Description: Optimize admin management of large networks
 * Version: 0.1.0
 * Author: Paul V. Biron/Sparrow Hawk Computing
 * Author URI: https://sparrowhawkcomputing.com
 * Plugin URI: https://github.com/pbiron/large-network-management
 * GitHub Plugin URI: https://github.com/pbiron/large-network-management
 * Release Asset: true
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: True
 */

defined( 'ABSPATH' ) || die;

require __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @todo currently, most of the functionality modification only applies to
 *       super_admins.  It might be useful to have it apply for any user that
 *       is a member of more than ~30 sites.
 */
class Large_Network_Management_Plugin extends _Large_Network_Management_Plugin {
	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_hooks() {
		// we are, by definition, a large network :-)
		// we set priority to 9, so other plugins that use the default priority
		// can also hook into 'wp_is_large_networ' after us.
		add_filter( 'wp_is_large_network', '__return_true', 9 );

		add_action( 'plugins_loaded', array( $this, 'setup' ) );

		return;
	}

	/**
	 * Perform setup operations.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function setup() {
		global $pagenow;

		if ( ! wp_is_large_network() ) {
			// not a large network, so nothing for us to do.
			return;
		}

		if ( is_admin() || is_admin_bar_showing() ) {
			Large_Network_Management_My_Sites::get_instance();
			Large_Network_Management_Get_Blogs_Of_User::get_instance();
		}
		if ( is_network_admin() && 'users.php' === $pagenow ) {
			Large_Network_Management_Network_Users::get_instance();
		}

		return;
	}

	/**
	 * Count the number of blogs a user has access to.
	 *
	 * @since 0.1.0
	 *
	 * @param int $user_id The User ID to count blogs for.
	 * @return int The number of blogs `$user_id` is a member of.
	 *
	 * @todo this method is currently not used.  It _might_ be useful
	 *       if/when we loosen the restriction of applying most of the functionality
	 *       if this plugin to super_admins.
	 */
	static function count_blogs_of_user( $user_id ) {
		global $wpdb;

		$count = 0;

		// the logic here is borrowed from get_blogs_of_user() where it gathers the
		// site_id of all sites a user has access to (before it does the potentially
		// expensive get_sites() on those ids.
		$keys = get_user_meta( $user_id );
		if ( empty( $keys ) ) {
			return $count;
		}

		$keys = array_keys( $keys );

		foreach ( $keys as $key ) {
			if ( 'capabilities' !== substr( $key, -12 ) ) {
				continue;
			}
			if ( $wpdb->base_prefix && 0 !== strpos( $key, $wpdb->base_prefix ) ) {
				continue;
			}
			$site_id = str_replace( array( $wpdb->base_prefix, '_capabilities' ), '', $key );
			if ( ! is_numeric( $site_id ) ) {
				continue;
			}

			$count++;
		}

		return $count;
	}
}

// instantiate ourselves.
Large_Network_Management_Plugin::get_instance();
