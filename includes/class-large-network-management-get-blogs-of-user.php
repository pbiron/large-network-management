<?php

defined( 'ABSPATH' ) || die;

/**
 * Modify the behavior of `get_blogs_of_user()`.
 *
 * For super_admins on large networks, `get_blogs_of_user()` is **very** inefficient.
 * Therefore, we short circuit it for super_admin's and pretend that their only site
 * is the main site.
 *
 * We also return the list of sites in alphabetical order by `blogname` (for all users),
 * instead of the default `blog_id`, which is more "user friendly".
 *
 * @since 0.1.0
 *
 * @todo The inefficiency is because `get_blogs_of_user()` does a `switch_to_blog()`
 *       for each blog the user is a member of in order to get its `blogname`
 *       and `siteurl`.  Maybe there is a way to refactor `get_blogs_of_user()`
 *       so that `switch_to_blog()` weren't necessary.  Investigate and possibly
 *       propose a patch to core, in which case much of this plugin wouldn't be necessary.
 */
class Large_Network_Management_Get_Blogs_Of_User extends _Large_Network_Management_Singleton_Base {
	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_hooks() {
		add_filter( 'pre_get_blogs_of_user', array( $this, 'short_circuit_super_admin_sites' ), 0, 3 );
		add_filter( 'get_blogs_of_user', array( $this, 'order_blogs_by_name' ), 10, 3 );

		return;
	}

	/**
	 * Filters the list of a user’s sites before it is populated.
	 *
	 * On a network of even a few hundred sites, almost any request in /wp-admin
	 * (or on the front-end if the admin_bar is showing) can take several seconds
	 * if the current user is a super_admin because of various calls to
	 * {@link https://developer.wordpress.org/reference/functions/get_blogs_of_user/ get_blogs_of_user()}.
	 *
	 * So, for super_admins, we short circuit the call to `get_blogs_of_user()`
	 * and pretend they are only members of the main site.
	 *
	 * The short circuiting only happends if `get_blogs_of_user()` is called from
	 * within a specific set of functions (filterable via 'shc-large-network-management-functions-to-skip').
	 *
	 * @since 0.1.0
	 *
	 * @param array $sites An array of site objects of which the user is a member.
	 * @param int $user_id User ID.
	 * @return array
	 *
	 * @filter pre_get_blogs_of_user
	 */
	function short_circuit_super_admin_sites( $sites, $user_id ) {
		if ( ! is_super_admin( $user_id ) ) {
			return $sites;
		}

		$backtrace = $this->debug_backtrace();

		// core calls of get_blogs_of_user() in these functions/methods result
		// in slow load times for admin screens.
		$expensive_calls = array(
			// generates the "My Sites" menu and indirectly calls get_blogs_of_user()::
			'_wp_admin_bar_init',
			// indirectly calls get_blogs_of_user().
			'get_dashboard_url',
			// WP_MS_Users_List_Table::column_blogs().
			array( 'WP_MS_Users_List_Table', 'column_blogs' ),
		);

		/**
		 * Filters the functions to skip when deciding whether to short circuit
		 * calls to {@link https://developer.wordpress.org/reference/functions/get_blogs_of_user/ get_blogs_of_user()}.
		 *
		 * Plugins that result in calls to `get_blogs_of_user()` for any admin screen (including
		 * the adminbar) should use this this hooks and add their function/method to the list.
		 *
		 * @since 0.1.0
		 *
		 * @param array $expensive_calls Array of functions to skip.
		 */
		$expensive_calls = apply_filters( 'shc_large_network_management_expensive_get_blogs_of_user_calls', $expensive_calls );

		$short_circuit = false;
		foreach ( $expensive_calls as $call ) {
			if ( in_array( $call, $backtrace ) ) {
				$short_circuit = true;
				break;
			}
		}

		if ( ! $short_circuit ) {
			// get_blogs_of_user() called by some function that does not impact
			// admin page load times, so let it do its thing.
			return $sites;
		}

		$main_site = get_site( get_main_site_id() );

		// if we're not in the main site, then this will result in 1 call to
		// switch_to_blog()/restore_current_blog() but that is much better
		// than 1 call for every blog which would otherwise happen for
		// super_admins.
		return array(
			$main_site->id => (object) array(
				'userblog_id' => $main_site->id,
				'blogname'    => $main_site->blogname,
				'domain'      => $main_site->domain,
				'path'        => $main_site->path,
				'site_id'     => $main_site->network_id,
				'siteurl'     => $main_site->siteurl,
				'archived'    => $main_site->archived,
				'mature'      => $main_site->mature,
				'spam'        => $main_site->spam,
				'deleted'     => $main_site->deleted,
			),
		);
	}

	/**
	 * Order blogs by name.
	 *
	 * By default, the My Sites menu lists sites by site->id.  It is much more
	 * "user friendly" to show them alphabetical by blogname.
	 *
	 * @since 0.1.0
	 *
	 * @param array $sites An array of site objects belonging to the user.
	 * @return array
	 *
	 * @filter get_blogs_of_user
	 */
	function order_blogs_by_name( $sites ) {
		usort( $sites, array( $this, 'compare_site_names' ) );

		return $sites;
	}

	/**
	 * Get an array of function/method calls from `debug_backtrace()`.
	 *
	 * For method calls, the value in the returned array is [class_name, method_name];
	 * for function calls, the value is simply the function name.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	protected function debug_backtrace() {
		$backtrace = array();

		foreach ( debug_backtrace() as $call ) {
			$backtrace[] = isset( $call['class'] ) ?
				array( $call['class'], $call['function'] ) :
				$call['function'];
		}

		return $backtrace;
	}

	/**
	 * Comparison function for debug_backtrace() calls.
	 *
	 * @since 0.1.0
	 *
	 * @param string|array $a
	 * @param string|array $b
	 * @return int
	 *
	 * @todo this was used by a previous version of @see self::short_circuit_super_admin_sites()
	 *       when array_uintersect() was used to decide whether get_blogs_of_user() was called
	 *       in an expensive manner.  I think the way that check is done is more efficient now,
	 *       hence, this method is not needed.  But I'm leaving this method in place for now,
	 *       just in case I determine otherwise.
	 */
	protected function compare_backtrace( $a, $b ) {
		if ( ! is_array( $a ) && ! is_array( $b ) ) {
			return strcasecmp( $a, $b );
		}
		elseif ( is_array( $a ) && ! is_array( $b ) ) {
			return -1;
		}
		elseif ( ! is_array( $a ) && is_array( $b ) ) {
			return 1;
		}

		$class_cmp = strcasecmp( $a[0], $b[0] );

		if ( 0 === $class_cmp ) {
			return strcasecmp( $a[1], $b[1] );
		}

		return $class_cmp;
	}

	/**
	 * Comparison function for self::order_blogs_by_name().
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Site $a
	 * @param WP_Site $b
	 * @return int < 0 if $a->blogname is less than $b->blogname;
	 *             > 0 if $a->blogname is greater than $b->blogname, and 0 if they are equal.
	 */
	protected function compare_site_names( $a, $b ) {
		return strcasecmp( $a->blogname, $b->blogname );
	}
}
