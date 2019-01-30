<?php

defined( 'ABSPATH' ) || die;

/**
 * For Super Admins, use Network Admin > Sites screen instead of My Sites.
 *
 * @since 0.1.0
 */
class Large_Network_Management_My_Sites extends _Large_Network_Management_Singleton_Base {
	function add_hooks() {
		add_filter( 'admin_init', array( $this, 'redirect_my_sites' ) );
		add_filter( 'admin_url', array( $this, 'my_sites_url' ), 10, 2 );

		return;
	}

	/**
	 * Redirect from My Sites to Netowrk Admin > Sites screen when current user is a super_admin.
	 *
	 * @since 0.1.0
	 *
	 * @return void When the redirect happens, exits.
	 */
	function redirect_my_sites() {
		global $pagenow;

		if ( ! ( is_super_admin() && 'my-sites.php' === $pagenow ) ) {
			return;
		}

		// perform the redirect.
		wp_redirect( network_admin_url( 'sites.php' ) );
		exit;
	}

	/**
	 * For super_admins, have the My Sites adminbar menu item link to the
	 * Network Admin > Sites screen.
	 *
	 * My Sites does not do any pagination.  Hence, for super_admins,
	 * it can be **very** inefficient.  The Network Admin > Sites screen provides all
	 * the info that My Sites does (and more) and is paginated, thus making it
	 * much more efficient for super_admins.
	 *
	 * @since 0.1.0
	 *
	 * @param string $url  The complete admin area URL including scheme and path.
	 * @param string $path Path relative to the admin area URL. Blank string if
	 *                     no path is specified.
	 * @return string
	 */
	function my_sites_url( $url, $path ) {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			// soemtimes we are called before pluggable.php is loaded,
			// which defines `wp_get_current_user()` which, in turn, is called by
			// `is_super_admin()`.  So, we have to load it ourselves.
			// @todo document the cases where we are called before pluggable.php
			//       is loaded.
			require ABSPATH . WPINC . '/pluggable.php';
		}

		if ( is_super_admin() && 'my-sites.php' === $path ) {
			$url = network_admin_url( 'sites.php' );
		}

		return $url;
	}
}
