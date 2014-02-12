<?php
/*
Plugin Name: WPcpt
Plugin URI: http://edpittol.github.io/wpcpt
Description: Wordpress custom post-type framework.
Author: Eduardo Pittol
Author URI: http://edpittol.github.io
Version: 0.1
*/

/*	Copyright 2013  Eduardo Pittol  (email : edpittol@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! class_exists( 'WPcpt' ) ) :

class WPcpt {
	
	public static $domain = 'wpcpt';

	public static function base_dir() {
		return plugin_dir_path( __FILE__ );
	}

	private static $instance = null;
	
	public static function instance() {
		is_null( self::$instance ) && self::$instance = new self;
		return self::$instance;
	}
	
	public static function init() {
		// load the plugin classes
		require_once 'lib/NHP-Theme-Options-Framework/options/options.php';
		require_once 'core/wpcpt-metabox.php';
		require_once 'core/wpcpt-post-type.php';
		require_once 'core/wpcpt-builtin-post-type.php';
		require_once 'core/wpcpt-taxonomy.php';
		require_once 'core/wpcpt-util.php';
		require_once 'config/wpcpt-config-field.php';
		require_once 'config/wpcpt-config-section.php';
		require_once 'config/wpcpt-config.php';
		require_once 'routing/wpcpt-config-route.php';
		require_once 'routing/wpcpt-route.php';
		require_once 'builtin/wpcpt-builtin-post.php';

		load_plugin_textdomain( 
			self::$domain, 
			false, 
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' 
		);
		
		do_action( 'wpcpt_loaded' );
	}
}

WPcpt::init();

endif;