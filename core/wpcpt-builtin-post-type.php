<?php

/**
 * A class that map a builtin post-type. This class doesn't execute the 
 * register_post_type function.
 */
abstract class WPcpt_Builtin_Post_Type extends WPcpt_Post_Type {
	
	/**
	 * Create a WPcpt_Post_Type using information of a registered post-type.
	 * 
	 * @param string $slug The slug of the registered post-type.
	 */
	public function __construct( $slug ) {
		$post_type_obj = get_post_type_object( $slug );
		
		parent::__construct( $post_type_obj->label, $slug);
		
		// actions
		remove_action( 'init', array( &$this, 'register_post_type' ) );
	}
	
	/**
	 * Ignore the path, because doesn't exist.
	 * 
	 * @see WPcpt_Post_Type::get_dir_path()
	 */
	public function get_dir_path() {
		return false;
	}

	/**
	 * Ignore the url, because doesn't exist.
	 *
	 * @see WPcpt_Post_Type::get_dir_url()
	 */
	public function get_dir_url() {
		return false;
	}
	
}