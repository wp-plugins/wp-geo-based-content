<?php
/*Class for public access*/
defined( 'ABSPATH' ) or die( 'No direct access please.' );
//require WPGEO_PLUGIN_DIR.'/core/wpgeo-class.php';

class PublicClass extends Wpgeo_Content
{
    public function __construct(){ 
        parent::__construct(); 
        $this->init_hooks();
    }
    
    function init_hooks(){
        /*Shortcode to load the localized contents*/
        add_shortcode('wpgeo_campaign', array($this, 'get_localized_content'));
    }
    
    /*
     * activated by add_shortcode function
     * returns the localized content based on the country.
     */
    function get_localized_content($atts){
        
        $atts = shortcode_atts(array('id'=>0), $atts);
        
        $user_country = $this->get_user_country();
        if(is_numeric($atts['id']) && $atts['id']){
            global $wpdb;
            $banners = $wpdb->get_results( 
                "SELECT * FROM $this->table_banners WHERE campaign_id=".$atts['id']." AND country_code='$user_country' ORDER BY banner_id DESC",
                "OBJECT" );
            
            if($banners == null && $user_country!='default'){
                $banners = $wpdb->get_results( 
                    "SELECT * FROM $this->table_banners WHERE campaign_id=".$atts['id']." AND country_code='default' ORDER BY banner_id DESC",
                    "OBJECT" );
            }
            
            if($banners != null){
                return do_shortcode(stripslashes($banners[0]->banner_content));
            }
            
        }else{
            return '';
        }
        
    }
}