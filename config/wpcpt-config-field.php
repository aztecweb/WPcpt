<?php

/**
 * 
 * A configuration field.
 */
class WPcpt_Config_Field {

	/**
	 * 
	 * @var string The field unique identifier.
	 */
	private $id;
	
	/**
	 * 
	 * @var string The name of the field shown in the admin screen.
	 */
	public $title;
	
	/**
	 * 
	 * @var array The args used to create a field in NHP_Options
	 */
	public $args;
	
	/**
	 * 
	 * Create a WPcpt_Config_Field. To be shown in the post-type
	 * configuration screen.
	 * 
	 * @param string $id The field unique identifier.
	 * @param string $title The title shown in the admin screen.
	 * @param array $args The args used to create a field in NHP_Options. If 
	 * 	type isn't passed, use text.
	 */
	public function __construct( $id, $title, $args = array() ) {
		$this->id = $id;
		$this->title = $title;
		
		if( ! isset( $args['type'] ) ) {
			$args['type'] = 'text';
		}
		
		$this->args = $args;
	}
	
	/**
	 * 
	 * Return a array with the field information. Must be compatible with the
	 * NHP_Option format for a field.
	 * 
	 * @return array The field information.
	 */
	public function to_array() {
		$args = $this->args;
		$args['id'] = $this->id;
		$args['title'] = $this->title;
		
		return $args;
	}
	
	/**
	 * 
	 * Get the field id.
	 * 
	 * @return string The field Id
	 */
	public function get_id() {
		return $this->id;
	}
}
