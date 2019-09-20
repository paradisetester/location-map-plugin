/*jQuery('.ordernow').click(function($){
	
	var popupdiv  = jQuery('.wploc_notification');
	
	jQuery('.black_overlay').show();
	popupdiv.show();
	jQuery('body').addClass('popup_overlay');
	popupdiv.addClass('active');	
	jQuery('.wploc_notification input[type="text"]').val('');
	
});*/


jQuery('.popup_loc_cls').click(function($){
	
	var popupdiv  = jQuery('.wploc_notification');
	
	jQuery('.black_overlay').hide();
	jQuery('.wploc_notification input[type="text"]').val('');
	popupdiv.hide();
	jQuery('body').removeClass('popup_overlay');
	popupdiv.removeClass('active');
});

jQuery(document).on('click', '#searchbtn_popup', function() {
	//var zipcode = jQuery('.postal_code').val();	
	var formdata  = jQuery(this).parents('.address_filed_form form').serialize();
	var parentdiv  = jQuery(this).parents('.address_filed_form').attr('id');
	
	RedirectWebsite(formdata,parentdiv);		
});

/*********submit location search*****************/
function RedirectWebsite(formdata,parentdiv){
	/* var formdata = {
		"action": "add_redirect_script",
		"zipcode": zipcode
	};	 */
	var addr = jQuery('.popup_address').val();
	var location_exist = true;
	var website_link = '';
	jQuery.ajax({
        type : "post",
        dataType : "json",
		url: wplocation_ajax.ajax_url,
		cache : false,
		data: formdata,
		success:function(response){				
		    if(response.status == 'success'){			
			

			jQuery.each( response.data, function( key, value ) {
			  
					area = value.latlng;
					polygon = new google.maps.Polygon({
						path: area,
					});
					geocoder.geocode({'address': addr}, function(results, status) {
				if (status === 'OK') {
						if (google.maps.geometry.poly.containsLocation(results[0].geometry.location, polygon)){
														
								var url = value.website;
								var cname = value.company_name;	
								
							if(location_exist==true)
							{						
								ResponseAction(location_exist,parentdiv,response.message,url,cname);
							}
							location_exist = false;		
								//return false;
								
						}else{
							location_exist = true;	
						} 
						
					} else {
						alert('Geocode was not successful for the following reason: ' + status);
					}
				});
				
					
			});				
			
			}else{
			
					console.log(location_exist);
					jQuery('#'+parentdiv+' .location-box').hide();					
					jQuery('#email-box').show();				
					jQuery('<p class="wploc_error_msg">'+response.message+'</p>').insertBefore('#'+parentdiv+' .location-box');
					
					var popupdiv  = jQuery('.wploc_notification');
					
					jQuery('.black_overlay').show();
					popupdiv.show();
					jQuery('body').addClass('popup_overlay');
					popupdiv.addClass('active');	
					jQuery('.wploc_notification #'+parentdiv+' input[type="text"]').val(''); 
					
				
			}
			return false;
		},
		error: function(xhr, settings, exception){			
			//alert('The update server could not be contacted test.');
		}	
	});
}



function ResponseAction(location_exist,parentdiv,msg,url,cname){
	
	if(location_exist)
				  {
			
						jQuery('#'+parentdiv+' input[type="text"]').val('');
						//jQuery('#'+parentdiv+' .location-box').html('<p class="wplocconnect_msg">'+msg+'</p>');
						//jQuery('<p class="wplocconnect_msg">'+msg+' '+cname+'</p>').insertBefore('#'+parentdiv+' .location-box');	
						jQuery('.response_message').html('<p class="wplocconnect_msg">'+msg+' '+cname+'</p>');
						setTimeout(function(){
						window.location.replace(url);				
						}, 3000); 
				  }
	
}
/*********submit location emails with zipcode*****************/

jQuery('#add_email').on('click',function(){
	jQuery('.wploc_error_msg').remove();	
var email = jQuery('#popup_email').val();
var zipcode = jQuery('#zip_code').val();
if(email !='' && zipcode !=''){
	var formdata = {
		"action": "add_email_location",
		"zipcode": zipcode,
		"email": email
	};			
	jQuery.ajax({
        type : "post",
        dataType : "json",
		url: wplocation_ajax.ajax_url,
		data: formdata,
		success:function(response){				
		    if(response.status == 'success'){				
					jQuery('.wploc_notification input[type="text"]').val('');
					jQuery('.wploc_notification #email-box').html('<p>'+response.message+'</p>');				
				}else if(response.status == 'error'){
					jQuery('<p class="wploc_error_msg">'+response.message+'</p>').insertAfter('#email-box');					
			}
		}
		
	
	});
}else{
jQuery('<p class="wploc_error_msg">Please enter the email and zipcode.</p>').insertAfter('#email-box');	
}
	return false;
});

jQuery('.postal_code').hide();
var placeSearch, autocomplete;

var componentForm = { 
	postal_code: 'short_name'
};
 var  geocoder;
window.onload = initMap;
function initMap() {
geocoder = new google.maps.Geocoder();
	}
	
function initAutocomplete(autocompletesWraps) {
	// Create the autocomplete object, restricting the search predictions to
	// geographical location types.
	var options = {
		types: ['address'],
		/* componentRestrictions: {
			country: 'us'
		} */
		
	};
	
	var inputs = jQuery('.popup_address');
    var autocompletes = [];
    for (var i = 0; i < inputs.length; i++) {
		var autocomplete = new google.maps.places.Autocomplete(inputs[i], options);
		autocomplete = new google.maps.places.Autocomplete(inputs[i], options);
		//autocomplete.setFields(['address_component']);
		//autocomplete.setFields(['geometry']);
		autocomplete.inputId = inputs[i].id;
		autocomplete.parentDiv = jQuery('#'+inputs[i].id).parents('.address_filed_form').attr('id');
		autocomplete.addListener('place_changed', fillInAddressFields);
		inputs[i].addEventListener("focus", function() {		
		geolocate(autocomplete);
		}, false);
		autocompletes.push(autocomplete);
    }

	

	
	// Avoid paying for data that you don't need by restricting the set of
	// place fields that are returned to just the address components.
	
	
	// When the user selects an address from the drop-down, populate the
	// address fields in the form.
	//autocomplete.addListener('place_changed', fillInAddressFields);
}

function fillInAddressFields() {

        jQuery('.wploc_error_msg').removeClass('is-valid is-invalid');
   	
        var place = this.getPlace();
		var postal_code = '';
		var city = '';
		var state = '';
		var country = '';
		var lat = place.geometry.location.lat();
		var lng = place.geometry.location.lng();
    
      for (var i = 0; i < place.address_components.length; i++) {

		var addressType = place.address_components[i].types[0];

		var val = place.address_components[i].long_name;

		//console.log("address Type " + addressType + " val " + val + " pd " + this.parentDiv);
		
		//console.log(addressType);
		jQuery('#'+this.inputId).find("."+addressType).val(val);

		jQuery('#'+this.inputId).find("."+addressType).attr('disabled', false);
		var parendID = this.parentDiv;
	
	
	// Get each component of the address from the place details,
	// and then fill-in the corresponding field on the form.
	for (var i = 0; i < place.address_components.length; i++) {
		var addressType = place.address_components[i].types[0];	
		if (addressType == 'postal_code') {		
			postal_code = place.address_components[i][componentForm[addressType]];
		}
		if (place.address_components[i].types[0] == "locality") {		
			city = place.address_components[i].long_name;
		}
		if (place.address_components[i].types[0] == "administrative_area_level_1") {		
			state = place.address_components[i].long_name;
		}
		if (place.address_components[i].types[0] == "country") {		
			country = place.address_components[i].long_name;
		}
					
	}
	
	if(city){
	
		jQuery('#'+parendID+' .postal_code').val(postal_code);
		jQuery('#'+parendID+' .form_city').val(city);
		jQuery('#'+parendID+' .form_state').val(state);
		jQuery('#'+parendID+' .form_lat').val(lat);
		jQuery('#'+parendID+' .form_long').val(lng);
		jQuery('#'+parendID+' .form_country').val(country);
		jQuery('.wploc_error_msg').remove();
		
		var formdata  = jQuery(this).parents('.address_filed_form form').serialize();
		var parentdiv  = jQuery(this).parents('.address_filed_form').attr('id');
	}else{
		jQuery('#'+parendID+' .postal_code').show();
		jQuery('<p class="wploc_error_msg error">"No Zipcode found for this location please enter zipcode manually"</p>').insertAfter('#'+parendID+' #searchbtn_popup');
		
	}

        }



    }



// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			var geolocation = {
				lat: position.coords.latitude,
				lng: position.coords.longitude
			};
			var circle = new google.maps.Circle(
			{center: geolocation, radius: position.coords.accuracy});
			autocomplete.setBounds(circle.getBounds());
		});
	}
}