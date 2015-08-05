<?php
/*
 * Core class for the plugin
 */
defined( 'ABSPATH' ) or die( 'No direct access please.' );
class Wpgeo_Content{
    /*Version of the plugin*/
    public $version;
    /*Plugin name*/
    public $plugin_name;
    /*directory of the plugin*/
    public $plugin_dir;
    /*table name of campaigns*/
    public $table_campaign;
    /*table name for banners*/
    public $table_banners;
    
    public function __construct(){
        global $wpdb;
        $this->version = WPGEO_VERSION;
        $this->plugin_name = 'WP Geo Based Content';
        $this->plugin_dir = WPGEO_PLUGIN_DIR;
        $this->table_campaign = $wpdb->prefix . "wpgeo_campaign";
        $this->table_banners = $wpdb->prefix . "wpgeo_banners";

    }
    
    /*returns all the countries of a campaign*/
    function countries_of_campaign($campaign){
        
        global $wpdb;
        $txt = '';
        if(!is_numeric($campaign))
            return '';
        
        $countries = $wpdb->get_results( 
            "SELECT country_code FROM $this->table_banners WHERE campaign_id=".$campaign,
            "OBJECT" );
        
        if($countries && count($countries)){
            foreach($countries as $key => $country){
                $txt .= ($key!=0 ?', ':'').($country->country_code == 'default' ? 'Worldwide' : $country->country_code);
            }
            return $txt;
        }else{
            return '';
        }
        
    }
    
    /*
     * gets the use IP address
     * converts IP to country code
     * returns country code
     */
    function get_user_country(){
        
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        /*Uses WordPress HTTP Class*/
        if( !class_exists( 'WP_Http' ) )
            include_once( ABSPATH . WPINC. '/class-http.php' );
        
        $request = new WP_Http;
        /*
         * get country info based on the ip of user
         * requests to the plugin server for ip to country code
         */
            $result = $request->request( "http://www.wpfetish.com/ip2nation.php/?ip=$user_ip" );     

        if(!empty($result['body'])){
            $res = json_decode($result['body']);
            if($res->status == 'success'){
                return strtoupper( $res->iso_code_2 );
            }
        }
        
        return 'default';
        
    }
    /*Displays the html for paypal donation section in plugin header*/
    function get_donatebutton(){
        ?>
        <span class="wpgeo-donation-title">Help us add more features</span>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="SFCCDXNDQFRSQ">
            <input type="image" src="https://www.paypalobjects.com/en_US/IL/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
        
    <?php 
    }
    
    /*Plugin install*/
    public static function wpgeo_install(){
    
        global $wpdb;
        $wpgeo_version = get_option('wpgeo_version');
        
        if (!$wpgeo_version) {
            $charset_collate = $wpdb->get_charset_collate();
            $table_campaign = $wpdb->prefix . "wpgeo_campaign";
            $table_banners = $wpdb->prefix . "wpgeo_banners";

            $sql_campaign = "CREATE TABLE $table_campaign (
                campaign_id int(11) NOT NULL AUTO_INCREMENT,
                campaign_title varchar(64) NOT NULL,
                campaign_shortcode BOOLEAN NOT NULL DEFAULT FALSE,
                PRIMARY KEY (campaign_id)
              ) $charset_collate;";

            $sql_banners = "CREATE TABLE $table_banners (
                banner_id int(11) NOT NULL AUTO_INCREMENT,
                campaign_id int(11) NOT NULL,
                country_code varchar(10) NOT NULL,
                banner_content longtext NOT NULL,
                PRIMARY KEY (banner_id)
              ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql_campaign);
            dbDelta($sql_banners);

            add_option('wpgeo_version', WPGEO_VERSION);
        }
        
    }
    /*Plugin uninstall*/
    public static function wpgeo_uninstall(){
        
       global $wpdb;
       $table_campaign = $wpdb->prefix . "wpgeo_campaign";
       $table_banners = $wpdb->prefix . "wpgeo_banners";
       
       $wpdb->query("DROP TABLE IF EXISTS $table_campaign, $table_banners");

       delete_option( 'wpgeo_version' );
       
    }
    
    
}