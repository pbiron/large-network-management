<?php

defined( 'ABSPATH' ) || die;

/**
 * Abstract base class for main plugin classes.
 *
 * @since 0.1.0
 */
abstract class _Large_Network_Management_Plugin extends _Large_Network_Management_Singleton_Base {
	/**
	 * Path to the main plugin file.
	 *
	 * Used in other classes to generate URLs for assets (with `plugins_url()`).
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Get the full path of the plugin main file.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_file() {
		return $this->file;
	}

	/**
	 * Get the dirname of the plugin main file.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_dirname() {
		return dirname( $this->file );
	}

	/**
	 * Get the basename of the main plugin file (i.e., the dirname).
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_basename() {
		return basename( $this->get_dirname() );
	}

	/**
	 * Get our slug (e.g., filename relative to plugins directory).
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	function get_slug() {
		return sprintf( '%s/%s', $this->get_basename(), basename( $this->file ) );
	}

	/**
	 * Clean up when we are deactivated.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 *
	 * @action deactivte_ . plugin_slug
	 */
	function deactivate() {
		// @todo do something

		return;
	}

	/**
	 * Clean up when we are uninstalled.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	static function uninstall() {
		// @todo do something

		return;
	}
}
