<?php

/**
 * 
 * A section of fields. Each section is a tab in the post-type configuration
 * page.
 */
class WPcpt_Config_Section {
	
	/**
	 * 
	 * @param WPcpt_Post_Type $post_type The post type that belongs to the 
	 * 	configuration.
	 */
	protected $post_type;

	/**
	 * 
	 * @var string The section unique identifier.
	 */
	private $id;
	
	/**
	 * 
	 * @var string The title shown in the admin screen.
	 */
	public $title;
	
	/**
	 * 
	 * @var array The args used to create a section in NHP_Options 
	 */
	protected $args = array();
	
	/**
	 * 
	 * @var WPcpt_Config_Field[] The 
	 * 	WPcpt_Config_Field in the section.
	 */
	protected $fields = array();
	
	/**
	 * Create a section.
	 * 
	 * @param WPcpt_Post_Type $post_type The post type that belongs to the 
	 * 	configuration.
	 * @param string $id The field unique identifier.
	 * @param string $title The title shown in the admin screen.
	 * @param array $args The args used tu create a section in NHP_Options.
	 */
	public function __construct( $post_type, $id, $title, $args = array() ) {
		$this->post_type = $post_type;
		$this->id = $id;
		$this->title = $title;
		$this->args = $args;
	}
	
	/**
	 * Add a field to the section.
	 * 
	 * @param WPcpt_Config_Field $field The field object.
	 * @throws Exception If the field isn't a WPcpt_Config_Field.
	 */
	public function add_field( $field ) {
		if( ! is_a( $field, 'WPcpt_Config_Field' ) ) {
			throw new Exception( 'Invalid field' );
		}
		
		$this->fields[$field->get_id()] = $field;
		
		return $this;
	}

	/**
	 * Add a field from the section.
	 *
	 * @param WPcpt_Config_Field $field The field object.
	 * @throws Exception If the field isn't a WPcpt_Config_Field.
	 */
	public function remove_field( $field ) {
		if( ! is_a( $field, 'WPcpt_Config_Field' ) ) {
			throw new Exception( 'Invalid field' );
		}
		
		unset( $this->fields[$field->get_id()] );
	}
	
	/**
	 * 
	 * Remove a field of the section by the identifier.
	 * 
	 * @param string $id The identifier.
	 */
	public function remove_field_by_id( $id ) {
		$this->remove_field( $this->fields[$id] );
	}
	
	/**
	 * Return a array with the field information. Must be compatible with the
	 * NHP_Option format for a section.
	 * 
	 * @return array The section information.
	 */
	public function to_array() {		
		$args = $this->args;
		
		$args['id'] = $this->id;
		$args['title'] = $this->title;
		$args['fields'] = array();
		
		foreach( $this->fields as $field ) {
			$args['fields'][] = $field->to_array();
		}
		
		// add a message if don't have any field
		if( count( $args['fields'] ) == 0 ) {
			if( empty( $args['desc'] ) ) {
				$args['desc'] = '';
			}
			
			$args['desc'] .= 
					__( '<p class="description">You can add fields here.</p>',
							WPcpt::$domain 
					);
		}
		
		return $args;
	}
	
	/**
	 * 
	 * Get the section id.
	 * 
	 * @return string The field Id
	 */
	public function get_id() {
		return $this->id;
	}
	
	public function get_arg( $key ) {
		if( ! isset( $this->args[$key] ) ) 
			return null;
		
		return $this->args[$key];
	}
}
