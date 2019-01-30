<?php

defined( 'ABSPATH' ) || die;

/**
 * Modify the behavior of the Network Admin > Users screen.
 *
 * @since 0.1.0
 */
class Large_Network_Management_Network_Users extends _Large_Network_Management_Singleton_Base {
	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function add_hooks() {
		add_filter( 'ms_user_list_site_actions', array( $this, 'site_actions' ), 10, 2 );

		return;
	}

	/**
	 * Modify site actions.
	 *
	 * @since 0.1.0
	 *
	 * @param array $actions An array of action links to be displayed. Default 'Edit', 'View'.
	 * @param int $userblog_id The site ID.
	 * @return array
	 */
	function site_actions( $actions, $userblog_id ) {
		$actions['dashboard'] = sprintf(
			'<a href="%s">%s</a>',
			get_admin_url( $userblog_id, '' ),
			__( 'Dashboard' )
		);

		return $actions;
	}
}
