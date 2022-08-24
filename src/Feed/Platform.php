<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

// Exit on direct access
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

defined( 'ABSPATH' ) || exit;

/**
 * @psalm-consistent-constructor
 */
class Platform {

	private $name;

	private $version;

	/**
	 * @var Platform
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Platform
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 * 
	 * @throws Exception
	 * 
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot serialize singleton' );
	}

	private function __construct() {
		if ( empty( $this->get_name() ) ) {
			$this->set_name( 'WooCommerce' );
		}

		if ( empty( $this->get_version() ) ) {
			$this->set_version( ShoppingFeedHelper::get_wc_version() );
		}
	}

	/**
	 * @return mixed
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @param mixed $version
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}
}
