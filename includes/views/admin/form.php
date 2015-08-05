<?php 
defined( 'ABSPATH' ) or die( 'No direct access please.' );
global $wpgeo_country; ?>
<div class="wrap wpgeo-new-campaign">
    <!--Hack to display the notices above the title <h2>-->
    <h2 id="notice-above-title"></h2>    
    <div class="inside alignright wpgeo-donation-block"> <?php $this->get_donatebutton(); ?> </div>
    <h2><span class="wpgeo-title"><?php echo $this->plugin_name; ?>: <?php echo (empty($mode) ? 'Add' : 'Edit');?></span></h2>

    <form action="<?php echo (empty($mode))? admin_url("admin.php?page=wpgeo-campaigns&action=add#pinned") : admin_url("admin.php?page=wpgeo-campaigns&action=edit&campaign=$mode#pinned"); ?>" method="post">
        <?php 
        if(empty($mode)){
            wp_nonce_field( 'add_campaign', 'wpgeo_campaing_nonce'); 
        }else{
            wp_nonce_field( 'update_campaign', 'wpgeo_campaing_nonce_'.$mode); 
        ?>
            <!-- Used by the ajax action to update the campaign title -->
            <input type="hidden" value="<?php echo $mode ?>" id="campaign_id" />
        <?php 
        }
        ?>
            
        <table class="form-table">
            <tr>
                <th>Campaign Name</th>
                <td>
                    <span id="pinned"></span>    
                    <fieldset class="campaign_title_field">
                        <input type="text" name="campaign_title" value="<?php echo (isset($campaign->campaign_id)?$campaign->campaign_title:'') ?>" class="regular-text<?php echo (empty($mode) ? '' : ' wpgeo_campaing_update' ) ?>" />
                        <span class="spinner"></span><br />
                    </fieldset>
                    
                </td>
            </tr>
        </table>
        
        <table class="form-table">
            <tr>
                <th>Country</th>
                <td>
                    <select name="banner_country">
                        <?php foreach ($wpgeo_country as $key => $name): ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($_POST['banner_country'])?($_POST['banner_country']==$key?'selected':''): '') ?> >
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Banner code</th>
                <td>
                    <?php wp_editor( '', 'banner_content' ); ?>
                </td>
            </tr>
            <tr>
                <th></th>
                <td><input type="submit" value="<?php echo ( 'Add ' ); ?>" class="button button-primary" /></td>
            </tr>
        </table>
    </form>
        
    <div id="poststuff">
        <?php if (isset($banners)): ?>
        <table class="form-table">
            <tr>
                <th></th>
                <td>
                    <?php 
                    if (count($banners) > 0):
                        foreach ($banners as $banner):
                    ?>
                        <div class="postbox">
                            <a href="<?php echo admin_url("admin.php?page=wpgeo-campaigns&action=removebanner&banner=$banner->banner_id"); ?>" class="hndle-remove"><i class="dashicons dashicons-no"></i></a>
                            <h3 class="hndle"><?php echo isset($banner->country_code) ? $wpgeo_country[$banner->country_code] : '' ?></h3>
                            <div class="inside">
                                <?php echo isset($banner->banner_content) ? htmlspecialchars( stripcslashes($banner->banner_content) ): '' ?>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else: ?>
                    <div class="postbox">
                        <div class="inside">
                            <p>No Banners added</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php endif; ?>
    </div>  
    <?php if(!empty($mode)): ?>
    <table class="form-table">
        <tr>
            <th></th>
            <td>
                <?php if(isset($campaign->campaign_id) && $campaign->campaign_shortcode): ?>
                <fieldset>
                    <label for="wpgeo-shortcode">Click on the icon next to the shortcode to copy to clipboard or copy this shortcode and paste it into your post, page, or text widget content</label><br />
                    <input type="text" id="wpgeo-shortcode" onclick="this.select();" readonly="readonly" class="regular-text" value="<?php echo "[wpgeo_campaign id='" . $mode . "']"; ?>">
                    <a href="javascript:void(0);" onclick="javascript:document.getElementById('wpgeo-shortcode').select();" id="copy-button" title="Copy to clipboard" data-clipboard-target="wpgeo-shortcode" ><i class="dashicons dashicons-admin-page"></i></a>
                </fieldset>
                <?php else: ?>
                <a href="<?php echo wp_nonce_url(admin_url("admin.php?page=wpgeo-campaigns&action=generate_shortcode&campaign=$campaign->campaign_id"), 'generate_shortcode_'.$campaign->campaign_id); ?>" class="button button-primary"> Generate Shortcode </a>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php endif; ?>
</div>