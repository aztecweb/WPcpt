<?php

/**
 * Map the post post-type.
 */
class WPcpt_Builtin_Post extends WPcpt_Builtin_Post_Type {
	
	/**
	 * @var WPcpt_Builtin_Post The instance of this class
	 */
	private static $instance = null;
	
	/**
	 * Construct the object.
	 */
	public function __construct() {
		parent::__construct( 'post' );
	}
	
	/**
	 * Create a instance of this class if doesn't exist, otherwise return the 
	 * instance.
	 * 
	 * @return WPcpt_Builtin_Post The instance of this class
	 */
	public static function instance() {
		is_null( self::$instance ) && self::$instance = new self;
		return self::$instance;
	}
}