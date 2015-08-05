<div class="wrap wpgeo-campaign-list">
    <h2 id="notice-above-title"></h2>  
    <div class="inside alignright wpgeo-donation-block"> <?php $this->get_donatebutton(); ?> </div>
    <h2>
        <span class="wpgeo-title"><?php echo $this->plugin_name; ?></span>
        <a href="<?php echo admin_url('admin.php?page=wpgeo-campaigns&action=add'); ?>" class="add-new-h2">Add New</a>
        <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=wpgeo-campaigns&action=removeall'), 'removeall_campaign' ); ?>" class="add-new-h2 wpgeo-remove" onclick="return confirm('Remove all the campaigns?');">Remove All</a>
    </h2>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <th colspan="2">Campaign Name</th>
                <th>Countries</th>
                <th colspan="3">Shortcode</th>
                <th colspan="2">Action</th>
            </tr>
        </thead>
        <?php 
        if(count($campaigns) > 0):
            foreach($campaigns as $campaign): ?>
            <tr>
                <td colspan="2"><?php echo $campaign->campaign_title; ?></td>
                <td><?php echo $this->countries_of_campaign($campaign->campaign_id); ?></td>
                <td colspan="3">
                <?php if($campaign->campaign_shortcode): ?>
                    <input type="text" onclick="this.select();" class="large-text" readonly="readonly" value="<?php echo "[wpgeo_campaign id='".$campaign->campaign_id."']";  ?>" />
                <?php endif; ?>
                </td>
                <td colspan="2">
                    <a href="<?php echo admin_url('admin.php?page=wpgeo-campaigns&action=edit&campaign='.$campaign->campaign_id); ?>" title="Edit">Edit</a>
                    &nbsp;|&nbsp;
                    <a href="<?php echo admin_url('admin.php?page=wpgeo-campaigns&action=clone&campaign='.$campaign->campaign_id); ?>" title="Clone this camapaign">Clone</a>
                    &nbsp;|&nbsp;
                    <a href="<?php echo wp_nonce_url ( admin_url('admin.php?page=wpgeo-campaigns&action=remove&campaign='.$campaign->campaign_id), 'remove_campaign_'.$campaign->campaign_id); ?>" onclick="return confirm('Your are about to delete a campaign.');" title="Remove permanently">Delete</a>
                </td>
            </tr>
            <?php 
            endforeach; 
        else: 
            echo "<tr><td>No data.</td></tr>";
        endif; ?>
    </table>
</div>