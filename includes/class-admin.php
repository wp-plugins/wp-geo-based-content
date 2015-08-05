<?php
/*
 * Class for the admin functionality of the plugin.
 */
defined( 'ABSPATH' ) or die( 'No direct access please.' );
class AdminClass extends Wpgeo_Content
{   
    public $notices = array();
    public $model;
    
    public function __construct(){ 
        parent::__construct(); 
        $this->init_hooks();
        $this->model = new Wpgeo_Model();
    }
    
    function init_hooks(){
        add_action( 'wpgeo_notices', array($this, 'wpgeo_notice') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
        add_action( 'wp_ajax_wpgeo_update_campaign', array($this, 'wpgeo_update_campaign_ajax') );
    }
    
    function admin_menu(){
        add_menu_page( 
                $this->plugin_name, 
                'WP GEO', 
                'manage_options', 
                'wpgeo-campaigns', 
                array($this, 'campaign_controller') 
            );
        add_submenu_page( 
                'wpgeo-campaigns', 
                'Add Campaign',
                'Add New Geo Shortcode', 
                'manage_options', 
                'wpgeo-campaigns&action=add', 
                array($this, 'campaign_controller') 
            );
    }
    
    function admin_scripts(){
        wp_enqueue_style('wpgeo-admin-style', plugins_url('wp_geo_based_content/includes/css/wpgeo-admin-style.css'));
        
        /*Scipts for admin*/
        wp_register_script('wpgeo-admin-zclip', plugins_url('wp_geo_based_content/includes/js/ZeroClipboard.min.js'), array(), '2.2.0', true );
        wp_register_script('wpgeo-admin-main', plugins_url('wp_geo_based_content/includes/js/wpgeo-admin-main.js'), array('jquery'), $this->version, true );
        /*Localizes a wpgeo-admin-main script with data for a JavaScript variable*/
        wp_localize_script( 'wpgeo-admin-main', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
    
    function campaign_controller(){
        global $wpdb, $wpgeo_country;
        /*Enqueue the zeroClipboar script*/
        wp_enqueue_script('wpgeo-admin-zclip');
        /*Enqueue the main admin javascript*/
        wp_enqueue_script('wpgeo-admin-main');
        
        $action = isset($_GET['action'])?$_GET['action']:'';
        switch($action){
            case 'add':
                
                if ( isset( $_POST['wpgeo_campaing_nonce'] ) && wp_verify_nonce( $_POST['wpgeo_campaing_nonce'], 'add_campaign' ) ){

                   $campaign_title = $_POST['campaign_title'];
                   if(trim($campaign_title)!=''){
                       
                       $campaign_id = $this->model->add_campaign(array('campaign_title'=>stripslashes($campaign_title)));
                       if( $campaign_id ){
                           
                           $this->notices[] = array( 'status' => 'updated', 'message' => 'Campaign added.');
                           if (trim($_POST['banner_content']) != '') {

                                $banner['campaign_id'] = $campaign_id;
                                $banner['country_code'] = $_POST['banner_country'];
                                $banner['banner_content'] = $_POST['banner_content'];
                                
                                $this->model->add_banner($banner);
                                $this->notices[] = array( 'status' => 'updated', 'message' => 'Content added.');
                            }

                            $this->show_form($campaign_id);
                            
                        }else{
                            
                            $this->notices[] = array( 'status' => 'error', 'message'=>'Failed to add, please check data and try again.');
                            $this->show_form(); 
                            
                        }
                        
                    }else{
                        
                        $this->notices[] = array( 'status' => 'error', 'message'=>'Campaign name cannot be empty.' );
                        $this->show_form(); 
                        
                    }

                }else{
                    
                    $this->show_form();
                    
                }
                
            break;
            
            case 'edit':
                
                $edit_id = isset($_GET['campaign'])?$_GET['campaign']:'';
                
                $campaign = $this->model->get_campaign($edit_id); 

                if ( isset( $_POST['wpgeo_campaing_nonce_'.$campaign->campaign_id] ) && wp_verify_nonce( $_POST['wpgeo_campaing_nonce_'.$campaign->campaign_id], 'update_campaign' ) && !empty($campaign)){
                    
                    $campaign_title = trim($_POST['campaign_title']);
                    
                    if($campaign_title!='' && $campaign_title != $campaign->campaign_title){
                        $this->model->update_campaign( array('campaign_title'=>$campaign_title), $campaign->campaign_id );
                        $this->notices[] = array( 'status' => 'updated', 'message'=>'Campaign name updated.' );
                    }
                    
                    if(trim($_POST['banner_content'])!=''){
                        
                        $banner = $this->model->get_banner(array('banner_country'=>$_POST['banner_country'], 'campaign_id'=>$edit_id));
                        
                        if(!empty($banner)){
                            $banner_content['banner_content'] = $_POST['banner_content'];

                            $this->model->update_banner($banner_content, $banner->banner_id);
                            $this->notices[] = array( 'status' => 'updated', 'message'=>'Content for <strong>'.$wpgeo_country[$_POST['banner_country']].'</strong> updated.' );
                        }else{
                            $banner_content['campaign_id'] = $campaign->campaign_id;
                            $banner_content['country_code'] = $_POST['banner_country'];
                            $banner_content['banner_content'] = $_POST['banner_content'];

                            $this->model->add_banner($banner_content);
                            $this->notices[] = array( 'status' => 'updated', 'message'=>'Content added.' );
                        }
                        
                    }else{
                        $this->notices[] = array( 'status' => 'update-nag', 'message'=>'Content cannot be empty.' );
                    }
                    
                }
                
                if(is_numeric($edit_id)){
                    
                    $this->show_form($edit_id);
                }else{
                    
                    $this->show_list();
                    
                }
                
            break;
            
            /*Clone a campaign and its banners*/
            case 'clone':
                
                $clone_id = isset($_GET['campaign']) ? $_GET['campaign'] : '';
                if(is_numeric($clone_id)){
                    
                    $campaign = $this->model->get_campaign($clone_id); 
                    
                    if(!empty($campaign)){
                        
                        $banners = $this->model->get_all_banners_by_campaing($clone_id);
                        
                        $cloned_campaign_id = $this->model->add_campaign(
                                array( 
                                    'campaign_title'=>stripslashes($campaign->campaign_title.'-copy'), 
                                    'campaign_shortcode'=>$campaign->campaign_shortcode
                                )
                            );
                        if( $cloned_campaign_id ) {
                            
                            if(count($banners) > 0){
                                
                                foreach($banners as $banner){
                                    
                                    $banner_data = array(
                                        'campaign_id' => $cloned_campaign_id,
                                        'country_code' => $banner->country_code,
                                        'banner_content' => $banner->banner_content
                                    );
                                    $this->model->add_banner( $banner_data );
                                    
                                } 
                                $this->notices[] = array( 'status' => 'updated', 'message'=>'Campaign cloned.' );
                            }
                            
                        }
                        
                        
                    }
                    
                }
                $this->show_list();
            break;
        
            /*Remove the banner*/
            case 'removebanner':
                
                $remove_id = isset($_GET['banner']) ? $_GET['banner'] : '';
                if(is_numeric($remove_id)){
                    
                    $banner = $this->model->get_banner_by_id($remove_id);
                    
                    if($banner!=null){
                        $this->model->delete_banner($remove_id);
                        $this->notices[] = array( 'status' => 'updated', 'message'=>'Content for <strong>'. $wpgeo_country[$banner->country_code] .'</strong> Removed.' );
                        
                        $campaign_banners = $this->model->get_all_banners_by_campaing($banner->campaign_id);

                        if( count($campaign_banners) < 0 || $campaign_banners == NULL){
                            $this->model->delete_campaign($banner->campaign_id);
                            $this->notices[] = array( 'status' => 'updated', 'message'=>'Empty campaign removed' );
                            $this->show_list();
                        }else{
                            $this->show_form($banner->campaign_id);
                        }
                        
                    }else{
                        $this->notices[] = array( 'status' => 'error', 'message'=>'Invalid Action.' );
                        $this->show_list();
                    }
                    
                }else{
                    $this->notices[] = array( 'status' => 'error', 'message'=>'Invalid Action.' );
                    $this->show_list();
                }
                
            break;
            
            /*Remove all campaigns and its corrsponding banners*/
            case 'removeall':
                
                check_admin_referer( 'removeall_campaign' );
               
                $wpdb->query("TRUNCATE $this->table_banners");
                $wpdb->query("TRUNCATE $this->table_campaign");
                $this->notices[] = array( 'status' => 'updated', 'message'=>'All the campaign and its contents are removed.' );
                $this->show_list();
                
            break;
        
            /*Remove the campaign*/
            case 'remove':
                
                $remove_id = isset($_GET['campaign']) ? $_GET['campaign'] : '';

                check_admin_referer( 'remove_campaign_'.$remove_id );
                if(is_numeric($remove_id)){

                    $campaign = $this->model->get_campaign($remove_id);

                    if($campaign!=null){
                        $this->model->delete_campaign($remove_id);
                        $this->model->delete_banners_by_campaign($remove_id);
                    }
                    $this->notices[] = array( 'status' => 'updated', 'message'=>'Campaign <strong>'.$campaign->campaign_title.'</strong> and its contents are removed.' );
                }
                $this->show_list();
                
            break;
            /*Generate the shortcode for the campaign*/
            case 'generate_shortcode':
                
                $campaign_id = isset($_GET['campaign']) ? $_GET['campaign'] : '';
                if(is_numeric($campaign_id)){
                    
                    check_admin_referer( 'generate_shortcode_'.$campaign_id );
                    $this->model->update_campaign( array('campaign_shortcode'=> 1),  $campaign_id);
                    
                    $this->notices[] = array( 'status' => 'updated', 'message'=>'Shortcode Generated.' );
                    $this->show_form($campaign_id);
                    
                }else{
                    $this->show_list();
                }
            break;
            
            default:
               $this->show_list();
        }
        
    }
    /*Includes add/edit form*/
    function show_form($mode = ''){
        if(!empty($mode)){
            
            $campaign = $this->model->get_campaign($mode);
            
            if($campaign!=null){
                $banners = $this->model->get_all_banners_by_campaing($mode);
            }
            
        }
        do_action('wpgeo_notices', $this->notices);
        include('views/admin/form.php');
    }
    
    /*Includes the list of campaigns*/
    function show_list(){
      
        $campaigns = $this->model->get_all_campaigns();
        do_action('wpgeo_notices', $this->notices);
        include('views/admin/lists.php');
    
    }
    
    /*
     * Display the error/warning/notices
     * Activated by wpgeo_notices action hook
     */
    function wpgeo_notice($notices){
       
        if(is_array($notices) && count($notices) > 0){
            foreach($notices as $notice){
                echo "<div class=\"{$notice['status']} wpgeo-notice\"> <p>{$notice['message']}</p></div>";
            }
            
        }
        
    }
    
    /*Called for ajax update action of campaign*/
    function wpgeo_update_campaign_ajax(){
        
        $campaign_id = intval($_POST['campaign']);
        $campaign_title = $_POST['wpgeo_campaign_title']; 
        $nonce = $_POST['wpgeo_nonce'];
        
        if(!wp_verify_nonce( $nonce, 'update_campaign' )){ 
            echo "{'status':'error', 'message':'Invalid Action'}";
            wp_die(); 
        }
        
        if(trim($campaign_title) == ''){
            echo "{'status':'error', 'message':'Campaign title cannot be empty'}";
            wp_die(); 
        }
        
        $campaign = $this->model->get_campaign($campaign_id); 
        if(!empty($campaign) && $campaign_title != $campaign->campaign_title){
            $this->model->update_campaign( array('campaign_title'=>$campaign_title), $campaign->campaign_id );
            echo "{'status':'success', 'message':'Campaign title updated'}";
        }
        wp_die(); 
    }

}