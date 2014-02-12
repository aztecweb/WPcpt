<?php

/**
 * 
 * Represents the post-type configuration page. Each post-type has a 
 * configuration page. 
 * 
 * All configurations must be passed before the admin_menu hook.
 */
class WPcpt_Config {
	
	/**
	 * 
	 * @var string The post-type slug to add the configuration page.
	 */
	private $cpt_slug;
	
	/**
	 * 
	 * @var NHP_Options The options object.
	 */
	private $options;
	
	/**
	 * 
	 * @var WPcpt_Config_Section[] The sections in the page.
	 */
	private $sections = array();
	
	/**
	 * Create a new configuration page.
	 * 
	 * @param string $cpt_slug The custom post-type slug.
	 */
	function __construct( $cpt_slug ) {
		$this->cpt_slug = $cpt_slug;
		
		$this->init();

		add_action( 'admin_menu', array( &$this, 'define_sections' ) );
	}
	
	/**
	 * 
	 * Add a section to the configuration page. The function args are the same
	 * to create the WPcpt_Config_Section object.
	 * 
	 * @param WPcpt_Config_Section $section The section object.
	 * @return WPcpt_Config This class object.
	 */
	public function add_section( $section ) {
		if( ! is_a( $section, 'WPcpt_Config_Section' ) ) {
			throw new Exception( 'Invalid Section' );
		}
		
		$this->sections[$this->cpt_slug . '_' . $section->get_id()] = $section;
		
		return $this;
	}
	
	/**
	 * 
	 * Add a field to a section in the configuration page.
	 * 
	 * @param string $section_id The section identifier.
	 * @param WPcpt_Config_Field $field The field object to add.
	 * @return WPcpt_Config This class object.
	 */
	public function add_field( $section_id, $field ) {
		$this->sections[$section_id]->add_field( $field );
	
		return $this;
	}
	
	/**
	 * 
	 * Return a array with all configuration information. Must be compatible 
	 * with the NHP_Option format for a section.
	 * 
	 * @return array The configuration.
	 */
	public function to_array() {
		$config = array();
		
		foreach( $this->sections as $id => $section ) {
			$config[$id] = $section->to_array();
		}
		
		return $config;
	}
	
	/**
	 * 
	 * Get a option value.
	 * 
	 * @param string $option The option (field) id.
	 * @return string The option value.
	 */
	public function get( $option ) {
		return $this->options->get( $option );
	}
	
	/**
	 * 
	 * Initialize the configuration page.
	 */
	public function init() {		
		$this->options = new NHP_Options(
			array(), array(
					'opt_name' => $this->cpt_slug . '-configuration',
					'page_slug' => $this->cpt_slug . '-configuration',
					'menu_title' => __( 'Configuration', WPcpt::$domain ),
					'page_title' => __( 'Configuration', WPcpt::$domain ),
					'page_cap' => 'manage_options',
					'dev_mode' => WP_DEBUG,
					'show_import_export' => false,
					'page_type' => 'submenu',
					'page_parent' => 'edit.php?post_type=' . $this->cpt_slug,
			) );
	}
	
	/**
	 * 
	 * Define the sections of the configuration page. This function is called
	 * in the admin_menu hook.
	 */
	public function define_sections() {
		$this->options->sections = $this->to_array();
	}
	
	/**
	 * 
	 * Get a section by a identifier.
	 * 
	 * @param string $id The identifier.
	 * @return WPcpt_Config_Section The section.
	 */
	public function get_section_by_id( $id ) {
		return $this->sections[$id];
	}
}
