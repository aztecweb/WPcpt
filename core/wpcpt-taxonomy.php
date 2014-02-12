<?php

/**
 * A class to map a Wordpress taxonomy. It can be associate to many Wordpress 
 * post-types.
 */
class WPcpt_Taxonomy {

	/**
	 * 
	 * @var string The taxonomy name.
	 */
    public $name;
    
    /**
     * 
     * @var string The taxonomy slug.
     */
    public $slug;
    
    /**
     * 
     * @var string[] The labels used in the admin screen.
     */
    protected $labels;


    /**
     *
     * @var array The taxonomy args.
     */
    protected $args;

    /**
     * 
     * Create a new taxonomy. Must be created before the init hook. The values
     * are passed to register_taxonomy function.
     * 
     * @param string $name The name of the taxonomy.
     * @param string $slug The slug of the taxonomy.
     * @param string[] $labels The labels used in the admin screen. Override the default values.
     * @param string[] $args The taxonomy args.	Override default values.
     */
    public function __construct( $name, $slug, $labels = array(), $args = array() ) {
        $this->name = $name;
        $this->slug = $slug;
        
        $this->labels = shortcode_atts( $this->default_labels(), $labels );
        $this->args = shortcode_atts( $this->default_args(), $args );

        // Register taxonomy before post-type
        add_action( 'init', array( &$this, 'register_taxonomy' ), 5 );
    }

    /**
     * 
     * The default labels used in register_taxonomy function.
     * 
     * @return array The default labels.
     */
    protected function default_labels() {
        return array(
	            'name' => sprintf( __( '%ss', WPcpt::$domain ), $this->name ),
	            'singular_name' => sprintf( __( '%s', WPcpt::$domain ), $this->name ),
	            'add_or_remove_items' => sprintf( __( 'Add or Remove %ss', WPcpt::$domain ), $this->name ),
	            'view_item' => sprintf( __( 'View %s', WPcpt::$domain ), $this->name ),
	            'edit_item' => sprintf( __( 'Edit %s', WPcpt::$domain ), $this->name ),
	            'search_items' => sprintf( __( 'Search %s', WPcpt::$domain ), $this->name ),
	            'update_item' => sprintf( __( 'Update %s', WPcpt::$domain ), $this->name ),
	            'parent_item' => sprintf( __( 'Parent %s:', WPcpt::$domain ), $this->name ),
	            'parent_item_colon' => sprintf( __( 'Parent %s:', WPcpt::$domain ), $this->name ),
	            'menu_name' => sprintf( __( '%ss', WPcpt::$domain ), $this->name ),
	            'add_new_item' => sprintf( __( 'Add New %s', WPcpt::$domain ), $this->name ),
	            'new_item_name' => sprintf( __( 'New %s', WPcpt::$domain ), $this->name ),
	            'all_items' => sprintf( __( 'All %ss', WPcpt::$domain ), $this->name ),
	            'separate_items_with_commas' => sprintf( __( 'Separate %ss with comma', WPcpt::$domain ), $this->name ),
	            'choose_from_most_used' => sprintf( __( 'Choose from %ss most used', WPcpt::$domain ), $this->name )
        );
    }

    /**
     * 
     * The default args used in register_taxonomy function.
     * 
     * Hook the filter wp_taxonomy_default_args before return.
     * 
     * @return array The default args.
     */
    protected function default_args() {
        return apply_filters( 'wp_taxonomy_default_args', array(
	            'labels' => $this->labels,
	            'hierarchical' => true,
	            'public' => true,
	            'show_ui' => true,
	            'show_admin_column' => true,
	            'show_in_nav_menus' => true,
	            'show_tagcloud' => true,
        ) );
    }

    /**
     * 
     * Register the taxonomy in the Wordpress.
     */
    public function register_taxonomy() {
        register_taxonomy( $this->slug, '', $this->args );
    }
}