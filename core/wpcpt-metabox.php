<?php

class WPcpt_Metabox {
	
	private $name;
	private $slug;
	private $post_type;
	private $fields;
	private $args;
	

	/**
	 * 
	 * array['args']
	 * 			['context']
	 * 			['priority']
	 * 
	 */
	public function __construct( $name, $slug, $post_type, $fields, $args = array() ) {
		$this->name = $name;
		$this->slug = $slug;
		$this->post_type = $post_type;
		$this->fields = $fields;
		$this->args = wp_parse_args( $this->default_args(), $args );
		
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
		add_action( 'save_post', array( &$this, 'save_data' ) );
	}
	
	public function add_meta_box() {
		add_meta_box(
				$this->slug,
				$this->name,
				array( &$this, 'meta_box' ),
				$this->post_type
		);
		
		if( empty( $_GET['post'] ) ) return;
		
		foreach( $this->fields as $key => $field ) {
			$this->fields[$key]['value'] = 
					get_post_meta( $_GET['post'], $field['id'], true );
		}
	}
	
	public function meta_box() {
		$options = new NHP_Options( array(), array(
			'opt_name' => $this->slug		
		) );
		
		// create a fictitious section to add the scripts
		$options->sections[0]['fields'] = $this->fields;
		$options->_enqueue();
		
		echo '<table class="form-table">';
		
		foreach( $this->fields as $field ) {
			echo '<tr>';
			echo '<th>' . $field['title'] . '</th>';
			echo '<td>';
			$options->_field_input($field);
			echo '</td>';
			echo '</tr>';
		}

		echo '</table>';
		
		wp_nonce_field( $this->slug . '_metabox', $this->slug . '_fields' );
	}
	
	public function save_data( $post_id ) {
		if( ! $this->can_save( $post_id ) ) {
			return $post_id;
		}
		
		// tudo ok, agora processa dados	
		foreach( $this->fields as $field ) {
			$value = $_POST[$this->slug][$field['id']];
			if( ! empty( $field['transform'] ) ) {
				// callback to change the value
				$value = call_user_func ( $field['transform'], $value );
			}
			update_post_meta( $post_id, $field['id'], $value );
		}
	
		return $post_id;
	}
	
	/**
	 * Verify if its all ok to save the data
	 * 
	 */
	public function can_save( $post_id ) {
		// verifica se foi passado o nonce e se está correto
		if ( empty( $_POST[$this->slug . '_fields'] ) ) return false;
		if ( ! wp_verify_nonce( $_POST[$this->slug . '_fields'], $this->slug . '_metabox' ) ) return false;
		
		// verifica se é rotiua de auto salvamaneto
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
	
		// verificar permissão
		if ( ! current_user_can( 'edit_post', $post_id ) ) return false;
		
		// If you must update a post from code called by save_post, make sure to verify the post_type
		// is not set to 'revision' and that the $post object does indeed need to be updated.
		// http://codex.wordpress.org/Function_Reference/wp_update_post - Caution! section
		$post = get_post( $post_id );
		if( get_post_type( $post_id ) == 'revision' ) return false;
		
		return true;
	}
	
	public function default_args() {
		return array(
				'context' => 'advanced',
				'priority' => 'default',
				'transform' => false
		);
	}
}
