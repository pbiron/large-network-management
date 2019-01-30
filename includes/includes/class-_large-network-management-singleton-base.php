<?php

defined( 'ABSPATH' ) || die;

/**
 * Abstract base class for singletons.
 *
 * @since 0.1.0
 */
abstract class _Large_Network_Management_Singleton_Base {
	/**
	 * Our static instances.
	 *
	 * @since 0.1.0
	 *
	 * @var array SHC_Singleton_Base subclasses
	 */
	static $instances;

	/**
	 * Get our instance.
	 *
	 * Calling this static method is preferable to calling the class
	 * constrcutor directly.
	 *
	 * @since 0.1.0
	 *
	 * @return _Large_Network_Management_Singleton_Base subclass instance
	 */
	static function get_instance() {
		// get "Late Static Binding" class name.
		$class = get_called_class();

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class;
		}

		return self::$instances[ $class ];
	}

	/**
	 * Constructor.
	 *
	 * Initialize our static instance and add hooks.
	 *
	 * Subclasses that need additional initialization, must call parent::__construct()
	 * in their constructor before doing that initialization.
	 *
	 * @since 0.1.0
	 */
	function __construct() {
		// get "Late Static Binding" class name.
		$class = get_called_class();

		if ( isset( self::$instances[ $class ] ) ) {
			return self::$instances[ $class ];
		}

		self::$instances[ $class ] = $this;

		if ( is_callable( array( $this, 'add_hooks' ) ) ) {
			$this->add_hooks();
		}
	}
}
