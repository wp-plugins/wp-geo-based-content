<?php
/*
Plugin Name: WP Geo Based Content
Plugin URI:  
Description: This plugin let you display different content for different audience based on their geo location
Version:     1.07
Author:      Lior Levy
Author URI:  
License:     GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) or die( 'No direct access please.' );

define( 'WPGEO_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define( 'WPGEO_VERSION', 1.07 );

require_once( WPGEO_PLUGIN_DIR . 'core/config.php' );
require_once( WPGEO_PLUGIN_DIR . 'core/wpgeo_content-class.php' );
require_once( WPGEO_PLUGIN_DIR . 'includes/class-model.php' );
require_once( WPGEO_PLUGIN_DIR . 'includes/class-admin.php' );
require_once( WPGEO_PLUGIN_DIR . 'includes/class-public.php' );

/*Registration and Unistallation hook*/
register_activation_hook( __FILE__, array('Wpgeo_Content', 'wpgeo_install') );
register_uninstall_hook( __FILE__, array('Wpgeo_Content', 'wpgeo_uninstall') );

function run_wpgeo(){
    
    if(is_admin()){
         new AdminClass();
    }
    new PublicClass();

}
add_action( 'init', 'run_wpgeo' );