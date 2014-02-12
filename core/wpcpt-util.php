<?php

class WPcpt_Util {
	
	/**
	 * 
	 * 
	 * @param unknown_type $object
	 * @param unknown_type $class_name
	 * @return boolean
	 */
	public static function is( $object, $class_name ) {
				
		if( is_a( $object, $class_name ) )
			return true;
		
		if( is_subclass_of( $object, $class_name ) )
			return true;
		
		return false;
	}
}