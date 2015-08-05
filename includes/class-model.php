<?php
/*
 * Model for the plugin, performs all the CRUD operations on campaigns and banners table
 */
defined( 'ABSPATH' ) or die( 'No direct access please.' );

class Wpgeo_Model extends Wpgeo_Content {

    public function __construct() {
        parent::__construct();
    }

    function get_all_campaigns() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM $this->table_campaign", "OBJECT" );
    }

    function get_campaign($id) {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM $this->table_campaign WHERE campaign_id=" . $id, "OBJECT");
    }

    function add_campaign($data) {
        global $wpdb;
        $res = $wpdb->insert($this->table_campaign, $data, array('%s'));
        if($res)
            return $wpdb->insert_id;
        else
            return false;
    }

    function update_campaign($data, $id) {
        global $wpdb;
        return $wpdb->update( $this->table_campaign, $data, array('campaign_id' => $id) );
    }

    function delete_campaign($id){
        global $wpdb;
        return $wpdb->query("DELETE FROM $this->table_campaign WHERE campaign_id=".$id);
    }
    
    /*
     * $data : type array
     * $data = array( 'campaign_id'=>integer, 'country_code'=>'', 'banner_content'=>'' );
     * 
     */

    public function add_banner($data) {
        global $wpdb;
        return $wpdb->insert($this->table_banners, $data, array('%d', '%s', '%s'));
    }
    
    public function get_banner($args){
        global $wpdb;
        return $wpdb->get_row( 
                        "SELECT * FROM $this->table_banners WHERE country_code = '".$args['banner_country']."' AND campaign_id=".$args['campaign_id'],
                        "OBJECT" );
    }
    
    function get_banner_by_id($id){
        global $wpdb;
        return $wpdb->get_row( 
                    "SELECT * FROM $this->table_banners WHERE banner_id=".$id,
                    "OBJECT" );
    }
    
    function get_all_banners_by_campaing($campaign_id){
        global $wpdb;
        return $wpdb->get_results( 
            "SELECT * FROM $this->table_banners WHERE campaign_id=".$campaign_id,
            "OBJECT" );
    }
    
    function update_banner($data, $id) {
        global $wpdb;
        return $wpdb->update( $this->table_banners, $data, array('banner_id' => $id) );
    }
    
    function delete_banners_by_campaign($campaign_id){
        global $wpdb;
        $wpdb->query("DELETE FROM $this->table_banners WHERE campaign_id=".$campaign_id);
    }
    
    function delete_banner($id){
        global $wpdb;
        $wpdb->query("DELETE FROM $this->table_banners WHERE banner_id=".$id);
    }
}