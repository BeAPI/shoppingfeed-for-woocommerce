<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

// Exit on direct access
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

defined( 'ABSPATH' ) || exit;

/**
 * @psalm-consistent-constructor
 */
class Uri {

	/**
	 * @var string $directory
	 */
	private $directory;

	/**
	 * @var string $file_name
	 */
	private $file_name;

	/**
	 * @var string $file_path
	 */
	private $file_path;

	/**
	 * @var boolean $is_compressed
	 */
	public $is_compressed = false;

	/**
	 * @var string[] $uri_pattern
	 */
	private static $uri_pattern = array(
		0 => 'file://%s.xml',
		1 => 'compress.zlib://%s.xml.gz',
	);

	/**
	 * @var Uri
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Uri
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
	 */
	private function __wakeup() {
	}

	/**
	 * Uri constructor.
	 */
	private function __construct() {
		if ( empty( $this->get_directory() ) ) {
			$directory       = ShoppingFeedHelper::get_feed_directory();
			$this->directory = $directory;
		}

		if ( empty( $this->get_file_name() ) ) {
			$this->set_file_name( ShoppingFeedHelper::get_feed_filename() );
		}

		if ( empty( $this->get_file_path() ) ) {
			$this->set_file_path();
		}
	}

	/**
	 * @return string
	 */
	public function get_uri() {
		return sprintf( self::$uri_pattern[ $this->is_compressed ], $this->file_path );
	}

	/**
	 * @return mixed
	 */
	public function get_directory() {
		return $this->directory;
	}

	/**
	 * @param mixed $directory
	 */
	public function set_directory( $directory ) {
		$this->directory = $directory;
	}

	/**
	 * @return mixed
	 */
	public function get_file_name() {
		return $this->file_name;
	}

	/**
	 * @param mixed $file_name
	 */
	public function set_file_name( $file_name ) {
		$this->file_name = $file_name;
	}

	/**
	 * @return mixed
	 */
	public function get_file_path() {
		return $this->file_path;
	}

	/**
	 * @param mixed $file_path
	 */
	public function set_file_path() {
		$this->file_path = $this->directory . $this->file_name;
	}

	/**
	 * @param boolean $is_compressed
	 */
	public function set_compressed( $is_compressed ) {
		$this->is_compressed = $is_compressed;
	}

	public static function get_full_path() {
		$uri       = new self();
		$extension = '.xml';
		if ( $uri->is_compressed ) {
			$extension .= '.gz';
		}

		return $uri->get_file_path() . $extension;
	}
}
