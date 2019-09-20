var $ = jQuery.noConflict();

jQuery(document).ready(function() {
  
jQuery('#providers-zip').dataTable( {
    "order": [],
    "columnDefs": [ {
      "targets"  : 'no-sort',
      "orderable": false,
    }]
});
  
} );

jQuery('#del_deletePostcodes').click(function(){
	if(jQuery(this).is(':checked')){
	jQuery('#providers-zip tbody input[type=checkbox]').prop('checked', true);
	}else{
	jQuery('#providers-zip tbody input[type=checkbox]').prop('checked', false);
	}
});

jQuery('#deletePostcodesTriger').click(function(){
	
	 if (confirm("Are you sure want to delete all post? This cannot be undone later.")) {
var searchIDs = jQuery("#providers-zip tbody input:checkbox:checked").map(function(){
      return jQuery(this).val();
    }).get(); 
	var formdata = {
						  "action": "DWPL",
						  "Postcodes": searchIDs,
						  "all":1
						};
						
					
   jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		   if(response.status == 'success'){
						jQuery.each(searchIDs,function(index,value){
							jQuery('#row-id-'+value).remove();
						}); 
						jQuery('.response').html('<div class="wpaas-notice notice updated"><p><strong>Success: &nbsp;</strong>'+response.message+'</strong>.</p></div>');
					}
		}
		});
	 }
});


function reply_click(clicked_id)
{
 if (confirm("Are you sure want to delete this post? This cannot be undone later.")) {
  var formdata = {
						  "action": "DWPL",
						  "post_id": clicked_id
						};
						
					
   jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		   if(response.status == 'success'){
						jQuery('#row-id-'+clicked_id).remove();
						jQuery('.response').html('<div class="wpaas-notice notice updated"><p><strong>Success: &nbsp;</strong>'+response.message+'</strong>.</p></div>');
					}
		}
		});
}
}

function click_delete_email(clicked_id)
{
 if (confirm("Are you sure want to delete this email? This cannot be undone later.")) {
  var formdata = {
						  "action": "DWPL_Email",
						  "post_id": clicked_id
						};
						
					
   jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		   if(response.status == 'success'){
						jQuery('#row-id-'+clicked_id).remove();
						jQuery('.response').html('<div class="wpaas-notice notice updated"><p><strong>Success: &nbsp;</strong>'+response.message+'</strong>.</p></div>');
					}
		}
		});
}
}

jQuery('#location_enter').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
    return false;
  }
});



jQuery('.wploc_searchbtn').click(function(){
	var searchval = jQuery('.wploc_zipsearch').val();
	 var data = {
		  "action": "WL_Searchzip",
		  "searchval": searchval
		};					
   jQuery.ajax({
        	type : "post",
       		dataType : "json",
			url: ajaxurl,
		data: data,
		success:function(response){
		if(response.status=="error"){
			jQuery('#zipcode_listing').html('');
			jQuery('.wpaas-notice').removeClass('updated');
			jQuery('.wpaas-notice').addClass('error');
		jQuery('.wpaas-notice').html('<p><strong>Note: &nbsp;</strong>'+response.message+'</p>');	
		}else{			
		  jQuery('#zipcode_listing').html(response.message);
		  jQuery('.wpaas-notice').removeClass('error');
		jQuery('.wpaas-notice').addClass('updated');
		  jQuery('.wpaas-notice').html('<p><strong>Note: &nbsp;</strong>Location Found</p>');
		
		  }
		}
		});
	 
});


function updateControls(addressComponents,lat,lng) {
	
                            $('#us5-street1').val(addressComponents.addressLine1);
                            $('#us5-city').val(addressComponents.city);
                            $('#us5-state').val(addressComponents.stateOrProvince);
                            $('#us5-zip').val(addressComponents.postalCode);
                            $('#us5-country').val(addressComponents.country);
                            $('#us5-lat').val(lat);
                            $('#us5-long').val(lng);
                        }
		
		setTimeout(function(){ 
			
				var lat =  $('#us5-lat').val();
				var lng =  $('#us5-long').val();	

					$('#us5').locationpicker({		
	
                            location: {
                                latitude: $('#us5-lat').val(),
                                longitude: $('#us5-long').val()
                            },
                            radius: 300,
                            onchanged: function (currentLocation, radius, isMarkerDropped) {
							var lat = $(this).locationpicker('map').location.latitude;
							var lng = $(this).locationpicker('map').location.longitude;
						
                             var addressComponents = $(this).locationpicker('map').location.addressComponents;
                                updateControls(addressComponents,lat,lng);
                            },
                            oninitialized: function (component) {
							
                                var address = $(component).locationpicker('map').location;
                                var addressComponents = $(component).locationpicker('map').location.addressComponents;
								
								var lat = address.latitude;
								var lng = address.longitude;
                                updateControls(addressComponents,lat,lng);
                            }
                        });
			}, 500);
		
		



