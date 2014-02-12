<?php

class WPcpt_Config_Route extends WPcpt_Config_Section {
	
	/**
	 * 
	 * @var WPcpt_Route[] Routes for the post-type
	 */
	public $routes = array();
	
	/**
	 * 
	 * @param unknown_type $post_type
	 * @param array $args
	 * 		[has_archive] (optional) Default: true
	 * 		[has_single] (optional) Default: true
	 * 		[template] (optional) The template file to load. Must be inner the 
	 * 			templates directory. Could be substitute by the theme put the 
	 * 			template inner "templates/{post type slug}/{template name}.
	 * 			Default: false
	 * 		+ parent args
	 */
	public function __construct( $post_type, $args = array() ) {
		$title = __( 'Routes', WPcpt::$domain );
		parent::__construct( $post_type, 'routes', $title, $args );

		$this->args = wp_parse_args( $args, $this->default_args() );
		$this->post_type_args();
		$this->configure_fields();
		$this->configure_routes();
	}
	
	public function default_args() {
		return array(
			'has_archive' => true,
			'has_single' => true,
			'template' => false
		);
	}
	
	/**
	 *
	 * Permite modificar as rotas através da página de configuração.
	 */
	public function configure_fields() {
		global $wp_rewrite;
		
		if( get_option( 'permalink_structure' ) == '' ) {
			$this->args['desc'] = 
					__( 'You must use a permalink structure to edit it.',
							WPcpt::$domain 
					);
										
		} else {
			$slug = $this->post_type->slug;
			
			if( $this->get_arg( 'has_single' ) === true ) {
				$this->add_field(
						new WPcpt_Config_Field(
								'route-single', 'Single', array(
										'std' => "/{$slug}/%{$slug}%"
								)
						)
				);
			}

			if( $this->get_arg( 'has_archive' ) === true ) {				
				$this->add_field(
						new WPcpt_Config_Field(
								'route-archive', 'Archive', array(
										'std' => $slug
								)
						)
				);
			}	
		}
	}
	
	public function post_type_args() {
		if( $this->get_arg( 'has_single' ) !== true ) {
			//$this->post_type->set_arg( 'publicly_queryable', false );
		}
		
		if( $this->get_arg( 'has_archive' ) === true ) {
			$this->post_type->set_arg( 'has_archive', true );
		} else {
			$this->post_type->set_arg( 'has_archive', false );
		}
	}
	
	public function configure_routes() {
		if( $this->get_arg( 'has_single' ) === true ) {
			$single_slug = 'route-single';
			$this->add_route( $single_slug, new WPcpt_Route(
				$this->post_type,
				$this->post_type->configuration->get( $single_slug ),
				array(
					'pagination' => true		
				)
			) );
		}
		
		if( $this->get_arg( 'has_archive' ) === true ) {
			$archive_slug = 'route-archive';
			$this->add_route( $archive_slug, new WPcpt_Route(
				$this->post_type,
				$this->post_type->configuration->get( $archive_slug ),
				array(
					'pagination' => true		
				)
			) );
		}
	}
	
	public function add_route( $slug, $route ) {
		$this->routes[$slug] = $route;
	}
}