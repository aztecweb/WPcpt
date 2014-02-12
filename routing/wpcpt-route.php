<?php

class WPcpt_Route {
	
	public $post_type;
	
	public $route_string;
	
	public $main_rule;
	
	public $args;
	
	private static $rule_pattern = '/%([^%]+)%/';
	
	private static $rule_replace = '([^/]*)';
	
	public static $matched_route;
	
	/**
	 * 
	 * @param unknown_type $configuration
	 * @param unknown_type $route_string
	 * @param unknown_type $template
	 * @param unknown_type $title
	 * @param unknown_type $post_type Post Type que vai junto com a rota. Se
	 * 	não for setado, não colocar como query var. Default: false
	 */
	public function __construct( $post_type, $route_string, $args = array() ) {
		
		$this->post_type = $post_type;
		$this->route_string = $route_string;
		$this->main_rule = self::convert_route( $this->route_string );
		$this->args = wp_parse_args( $args, $this->default_args() );

		add_filter( 'query_vars', array( &$this, 'query_vars' ) );
		add_filter( 'rewrite_rules_array', 
				array( &$this, 'add_rewrite_rule' ) );
		
		add_action( 'parse_request', array( &$this, 'parse_request' ) );
	}
	
	public function default_args() {
		return array(
				'template' => false,
				'title' => false,
				'scripts' => array(),
				'styles' => array(),
				'query_string' => false,
				'pagination' => false,
				'extra_vars' => array()
		);
	}
	
	public function set_arg( $slug, $value ) {
		if( array_key_exists( $slug , $this->args ) ) {
			$this->args[$slug] = $value;
			return true;
		}
		
		return false;
	}
	
	public function add_script( $script ) {
		$this->args['scripts'][] = $script;
	}
	
	public function add_style( $style ) {
		$this->args['styles'][] = $script;
	}
	
	public function load_template() {
		// add scripts and styles
		if( is_array( $this->args['scripts'] ) ) {
			foreach( $this->args['scripts'] as $i => $script ) {
				$handle = $this->post_type->slug . '-' .
						wp_hash( $this->template . $i );
		
				wp_enqueue_script(
					$handle,
					$this->post_type->get_dir_url() . '/js/' . $script['src'],
					$script['deps']
				);
		
				if( ! empty( $script['localize'] ) ) {
					wp_localize_script(
							$handle,
							$script['localize']['object_name'],
							$script['localize']['l10n']
					);
				}
			}
		}
		
		if( is_array( $this->args['styles'] ) ) {
			foreach( $this->args['styles'] as $i => $style ) {
				wp_enqueue_script(
						$this->post_type->slug . '-' .
								wp_hash( $this->template . $i ),
						$this->post_type->get_base_path() . '/css/' .
						$style['src'],
						$style['deps']
				);
			}
		}
		
		if( $this->args['template'] ) {
			global $template;
			
			$template = $this->post_type->get_template_file( 
					$this->args['template']
			);
			exit;
		}
	}	
	
	public function title( $title, $sep, $seplocation ) {
		$title_array = explode( $sep, $title );

		$page = "";
		$title = $title_array[count($title_array) - 1];
			
		if( count($title_array) == 2 ) {
			$page = $title_array[count($title_array) - 1];
			$title = $title_array[count($title_array) - 2];
		}
			
		if( $page ) {
			$page = " {$sep} {$page}";
		}
		
		if( $seplocation == 'right' ) {
			return "{$this->page_title()} {$sep} {$title}{$page}";
		}
		
		return "{$title_array[0]} {$sep} {$this->page_title()}{$page}";
	}
	
	public function page_title() {
		if( $this->args['title'] ) {
			$page_title = $this->args['title'];
			
			$vars = $this->get_variables( $this->route_string, $page_title );
			
			foreach( $vars as $var => $value ) {
				$page_title = preg_replace(
						'/%' . $var . '%/' , $value, $page_title );
			}
			
			return $page_title;
		}
		
		return false;
	}
	
	/**
	 * Get all rules generate from the main_rule.
	 * 
	 * @return array The array with the generated rules.
	 */
	public function get_rules() {
		$rules = array();
		$query_vars = array();
		$matches_index = 0;
		
		$rule = $this->main_rule;
		
		$query_vars['post_type'] = $this->post_type->slug;
		
		preg_match_all( self::$rule_pattern, $this->route_string, $matches);
		foreach( $matches[1] as $i => $var ) {
			$query_vars[$var] = '$matches[' . ++$matches_index  . ']';
		}
		$rewrite = 'index.php?';
		
		// remove post_type var if is single and add page option
		if( array_key_exists( $this->post_type->slug, $query_vars ) &&
				$this->args['pagination'] ) {
			unset( $query_vars['post_type'] );
				
			$query_vars['page'] = '$matches[' . ++$matches_index  . ']';
			$rule = str_replace( '/?$' , '(/[0-9]+)/?$', $rule );
		}
		
		$query_vars = array_merge( $query_vars, $this->args['extra_vars'] );
		
		$tmp_vars = array();
		foreach( $query_vars as $k => $v ) {
			$tmp_vars[] = $k . '=' . $v;
		}
		
		$rewrite .= implode( '&', $tmp_vars );
		
		$rules[$rule] = $rewrite;
		
		if( $this->args['pagination'] ) {
			$pagination_rule =
					str_replace( '?$' , 'page/([0-9]+)/?$', $rule );
			$pagination_rewrite =
			$rewrite . '&paged=$matches[' . ++$matches_index . ']';
				
			$rules[$pagination_rule] = $pagination_rewrite;
		}
		
		return $rules;
	}

	/**
	 * Build a url based in the route configuration.
	 */
	public function build_url( $args = array() ) {
		$url = $this->route_string;
		preg_match_all( self::$rule_pattern, $url, $matches );
		foreach( $matches[1] as $match ) {
			if( isset( $args[$match] ) ) {
				$url = str_replace( "%{$match}%" , $args[$match], $url );
			}
		}
		
		return home_url( $url );
	}
	
	/**
	 * Add the route rules for the wordpress routing. This is executed when the
	 * permalinks are upddated or the or the flush_rewrite_rules function is
	 * called. If the arg pagination is true, is created a rule with the same
	 * path plus the pagination.
	 * 
	 * @param array $rules The existents rules.
	 * @return array The existents rules plus the new rules
	 */
	public function add_rewrite_rule( $rules ) {
		return array_merge( $this->get_rules(), $rules );
	}


	public function parse_request( &$wp ) {
		foreach( $this->get_rules() as $rule => $query_string ) {
			if( $rule == $wp->matched_rule ) {
				self::$matched_route = $this;
					
				parse_str( $wp->matched_query, $wp->query_vars );
				
				if( $this->args['title'] ) {
					add_filter( 'wp_title', array( &$this, 'title' ), 10, 3 );
				}
				
				add_action( 'template_redirect', 
						array( &$this, 'load_template' ) );
			}
		}
	}
	
	function query_vars( $qvars ) {
		array_merge( $qvars, array_keys( $this->args['extra_vars'] ) );
		return $qvars;
	
	}
	
	public static function convert_route( $route_string ) {
		return preg_replace(
				self::$rule_pattern,
				self::$rule_replace,
				$route_string
			) . '/?$';
	}
	
	public function get_variables( $route_string, $replace_string ) {
		$variables = array();
		
		preg_match_all( self::$rule_pattern, $replace_string, $matches );
		foreach( $matches[1] as $match ) {
			if( get_query_var( $match ) ) {
				$cpt_slug = $this->post_type->slug;
				$value = get_query_var( $match );
				
				if( $match == $cpt_slug ) {
					$query = new WP_Query( array(
						'post_type' => $cpt_slug,
						'name' => $value
					));
				
					if( $query->posts ) {
						$variables[$match] = $query->post->post_title;
						continue;
					}
				}
				
				if( get_object_taxonomies( $cpt_slug ) ) {
					$term = get_term_by( 'slug', $value, $match );
					$variables[$match] = $term->name;
				}
			}
		}
		
		return $variables;
	}
}