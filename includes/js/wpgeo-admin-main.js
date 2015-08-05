/*ZeroClipboard activation*/
if(document.getElementById("copy-button") != null){
    var client = new ZeroClipboard(document.getElementById("copy-button"));

    client.on("ready", function (readyEvent) {

        client.on("aftercopy", function (event) {
            // `this` === `client`
            // `event.target` === the element that was clicked
            event.target.title = "Copied";
            event.target.innerHTML = '<i class="dashicons dashicons-yes"></i>';
        });
    });
}

/*jQuery to call the campaign save action*/
jQuery('.wpgeo_campaing_update').on('blur', function(){
    
    var wpgeo_campaign_title = this.value;
    var wpgeo_campaign = jQuery('#campaign_id').val();
    var wpgeo_nonce = jQuery('#wpgeo_campaing_nonce_'+wpgeo_campaign).val();
    
    jQuery('.campaign_title_field .spinner').css('visibility', 'visible');
    
    if(typeof(wpgeo_nonce) == 'undefined'){
        console.error('Invalid Action, wordpress nonce not found');
        jQuery('.campaign_title_field .spinner').css('visibility', 'hidden');
        return;
    }
    
    if(wpgeo_campaign_title.trim() != ''){
        
        var data = { 'action': 'wpgeo_update_campaign', 'wpgeo_nonce': wpgeo_nonce, 'wpgeo_campaign_title': wpgeo_campaign_title, 'campaign': wpgeo_campaign }
        jQuery.post(ajax_object.ajax_url, data, function(response) {
           jQuery('.campaign_title_field .spinner').css('visibility', 'hidden');
        });
        
    }else{
        jQuery('.campaign_title_field .spinner').css('visibility', 'hidden');
        alert('Campaign Title cannot be empty');
    }
    
    
});