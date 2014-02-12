<?php

/**
 * 
 * A class to map a Wordpress custom post type.
 */
abstract class WPcpt_Post_Type {
	
	/**
	 * 
	 * @var string The name of the custom post type.
	 */
	public $name = '';
	
	/**
	 * 
	 * @var string The slug of the custom post type.
	 */
	public $slug = '';
	
	/**
	 * 
	 * @var array The args used in the register_post_type function.
	 */
	public $args = array();
	
	/**
	 * 
	 * @var WPcpt_Taxonomy[] The taxononomies belonging to this custom post type.
	 */
	public $taxonomies = array();
	
	/**
	 * 
	 * @var WPcpt_Config The configuration page for this custom 
	 * post type.
	 */
	public $configuration = null;
	
	public $metaboxes = array();

	abstract protected function get_dir_path();
	abstract protected function get_dir_url();
	
	/**
	 * 
	 * Create a custom post type.
	 * 
	 * @param string $name The name of the custom post type.
	 * @param string $slug The slug of the custom post type.
	 * @param string[] (Optional) $labels The labels used in the admin screen.
	 * @param array $args (Optional) The args used in the register_post_type 
	 * 	function.
	 * 		[route] => (optional) WPcpt_Config_Route
	 * @param array $rewrite (Optional) The rewrite args in register_post_type 
	 * 	function.
	 */
	public function __construct( $name, $slug, $labels = array(), 
			$args = array(), $rewrite = array() ) {
		$this->name = $name;
		$this->slug = $slug;
		$this->configuration = new WPcpt_Config( $this->slug );

		$this->args['labels'] = $labels;
		$this->args['rewrite'] = $rewrite;
		$this->args = wp_parse_args( $args, $this->default_args() );
		
		// actions
		add_action( 'init', array( &$this, 'setup_args' ) );
		add_action( 'init', array( &$this, 'register_post_type' ) );
	}
	
	/**
	 * 
	 * Register a custom post type in Wordpress and relate with its taxonomies.
	 */
	public function register_post_type() {		
		register_post_type( $this->slug, $this->args );
		
		// add the taxonomies for the post-type
		foreach( $this->taxonomies as $taxonomy ) {
			register_taxonomy_for_object_type( $taxonomy->slug, $this->slug );
		}
	}
	
	public function setup_args() {
		if( ! WPcpt_Util::is( $this->args['route'], 'WPcpt_Config_Route' ) ) {
			$this->args['route'] = new WPcpt_Config_Route( $this );
		}
		
		$this->configuration->add_section( $this->args['route'] );
	}
	
	/**
	 * 
     * The default labels used in register_post_type function.
     * 
     * Hook the filter wp_custom_post_type_default_labels before return.
     * 
     * @return string[] The default labels.
     */
	public function default_labels() {
		return apply_filters( 'wp_custom_post_type_default_labels', array(
				'name' => sprintf( __( '%ss', WPcpt::$domain ), $this->name ),
				'singular_name' => sprintf( __( '%s', WPcpt::$domain ), $this->name ),
				'view_item' => sprintf( __( 'View %s', WPcpt::$domain ), $this->name ),
				'edit_item' => sprintf( __( 'Edit %s', WPcpt::$domain ), $this->name ),
				'search_items' => sprintf( __( 'Search %s', WPcpt::$domain ), $this->name ),
				'update_item' => sprintf( __( 'Update %s', WPcpt::$domain ), $this->name ),
				'parent_item_colon' => sprintf( __( 'Parent %s:', WPcpt::$domain ), $this->name ),
				'menu_name' => sprintf( __( '%ss', WPcpt::$domain ), $this->name ),
				'add_new' => __( 'Add New', WPcpt::$domain ),
				'add_new_item' => sprintf( __( 'Add New %s', WPcpt::$domain ), $this->name ),
				'new_item' => sprintf( __( 'New %s', WPcpt::$domain ), $this->name ),
				'all_items' => sprintf( __( 'All %ss', WPcpt::$domain ), $this->name ),
				'not_found' => sprintf( __( 'No %s found', WPcpt::$domain ), $this->name ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash', WPcpt::$domain ), $this->name ),
		) );
	}

	/**
	 * 
	 * The default args used in register_post_type function.
	 *
	 * Hook the filter wp_custom_post_type_default_args before return.
	 *
	 * @return array The default args.
	 */
	public function default_args() {
		return apply_filters( 'wp_custom_post_type_default_args', array(
				'labels' => wp_parse_args( $this->args['labels'], 
						$this->default_labels() ),
				'rewrite' =>  wp_parse_args( $this->args['rewrite'], 
						$this->default_rewrite() )
		) );
	}
	
	/**
	 * 
	 * The default rewrite args used in register_post_type function.
	 *
	 * Hook the filter wp_custom_post_type_default_rewrite before return.
	 * 
	 * @return array The default rewrite args.
	 */
	public function default_rewrite() {		
		return apply_filters( 'wp_custom_post_type_default_rewrite', array(
				'slug' => $this->configuration->get( 'route-archive' ),
				'with_front' => true,
				'feeds' => true,
				'pages' => true,
				'ep_mask' => EP_PERMALINK,
		) );
	}
	
	/**
	 * 
	 * Add a taxonomy to the custom post type.
	 * 
	 * @param WPcpt_Taxonomy $taxonomy
	 */
	public function add_taxonomy( $taxonomy ) {
		$this->taxonomies[$taxonomy->slug] = $taxonomy;
	}
	
	public function add_metabox( $name, $slug, $fields ) {
		$this->metaboxes[] = new WPcpt_Metabox( $name, $slug, $this->slug, $fields );
	}
	
	public function set_arg( $key, $value ) {
		$this->args[$key] = $value;
	}
	
	/**
	 * The template file to load. Must be inner the templates directory. Could 
	 * 	be substitute by the theme put the template inner 
	 * "templates/{post type slug}/{template name}.
	 */
	public function get_template_file( $template, $require = true ) {
		$theme_base = "templates/{$this->slug}";
		
		$dirs = array(
			STYLESHEETPATH . '/' . $theme_base, // child theme
			TEMPLATEPATH . '/' . $theme_base, // parent theme
			$this->get_dir_path() . '/templates' // post-type dir
		);
		
		foreach( $dirs as $dir ) {
			$file = $dir . '/' . $template . ".php";
			if( file_exists( $file ) ) {
				if( $require ) {
					require $file;
				}
				return $file;
			}
		}
		
		wp_die( "Couldn't load the {$template} file." );
	}
	
	/**
	 * Build a url based in the route configuration.
	 */
	public function build_url( $route_slug, $args = array() ) {
		foreach( $this->args['route']->routes as $slug => $route ) {
			if( $route_slug == $slug ) {
				return $route->build_url( $args );
			}
		}
	}
}
