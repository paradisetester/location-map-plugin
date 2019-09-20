var map;
var geocoder; 
var marker;
var polygon;
var bounds;
window.onload = initMap;
function initMap() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: center,
		zoom: 14,
		scaleControl: true
	});
	geocoder = new google.maps.Geocoder(); 
	bounds = new google.maps.LatLngBounds();
	google.maps.event.addListenerOnce(map, 'tilesloaded', function(evt) { 
		bounds = map.getBounds();
	});
	
	

	marker = new google.maps.Marker({
			position: center
		});
	polygon = new google.maps.Polygon({
		path: area,
		geodesic: true,
		strokeColor: '#FFd000',
		strokeOpacity: 1.0,
		strokeWeight: 4,
		fillColor: '#FFd000',
		fillOpacity: 0.35,
		editable: true,
		draggable: true,
	});
  
	polygon.setMap(map);	
	
/* 	google.maps.event.addListener(polygon.getPath(), 'insert_at', function(index, obj) {
		var logStr = ""
		for (var i = 0; i < this.getLength(); i++) {
			logStr += this.getAt(i).toUrlValue(6) + " ";
		}
		console.log(logStr);	  
	});
	 */
    google.maps.event.addListener(polygon.getPath(), 'set_at', function(index, obj) {	
	var logStr = []
    for (var i = 0; i < this.getLength(); i++) {
      logStr.push(this.getAt(i).toUrlValue(6)+' ');
    }		
	$('#us5-latlong').val(logStr);  	 
    });
	
		var input = (document.getElementById('pac-input'));
        var types = document.getElementById('type-selector');
		
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);

        var autocomplete = new google.maps.places.Autocomplete(input);	
		

        autocomplete.addListener('place_changed', function() {
		marker.setMap(null);
		var place = autocomplete.getPlace();
		console.log(place);
		var newBounds = new google.maps.LatLngBounds(bounds.getSouthWest(), bounds.getNorthEast()); 
		
          if (!place.geometry) {
						geocodeAddress(input.value);
            return;
          };
		  marker.setPosition(place.geometry.location);
		  marker.setMap(map);
		  newBounds.extend(place.geometry.location);
		  map.fitBounds(newBounds);
		  if (google.maps.geometry.poly.containsLocation(place.geometry.location, polygon)){
			alert('The area contains the address');  
		  } else {
			alert('The address is outside of the area.');  
		  };
	   });
}

function geocodeAddress(addr) {
	geocoder.geocode({'address': addr}, function(results, status) {
	  if (status === 'OK') {
  		var newBounds = new google.maps.LatLngBounds(bounds.getSouthWest(), bounds.getNorthEast());
		  marker.setPosition(results[0].geometry.location);
		  marker.setMap(map);
		  newBounds.extend(results[0].geometry.location);
		  map.fitBounds(newBounds);
		  if (google.maps.geometry.poly.containsLocation(results[0].geometry.location, polygon)){
			alert('The area contains the address');  
		  } else {
			alert('The address is outside of the area.');  
		  };
	  } else {
		alert('Geocode was not successful for the following reason: ' + status);
	  }
	});
}; 
//*************************

var center = new google.maps.LatLng(39.515900, -104.788840);

var area='';
if(latlong){	
	area = latlong;
	console.log('---');
}else{
 area= [
			{lat: 39.511153 , lng: -104.798661},
			{lat: 39.520615 ,lng: -104.80248},
			{lat: 39.526553 , lng: -104.788581},
			{lat: 39.520064, lng: -104.775577},
			{lat: 39.510595, lng: -104.781267}
		  ];
}

console.log(area);

jQuery('.add_location').on('click',function(){
	var formdata = {
		"action": "add_location_map",
		"id": $('#us5-id').val(),		
		"latlong": $('#us5-latlong').val(),		
		"lat": $('#us5-lat').val(),		
		"lng": $('#us5-long').val(),		
		"zipcode": $('#us5-zip').val(),		
		"website": $('#us5-website').val(),		
		"company_name": $('#us5-company').val(),		
		"addpost": $('.add_location').val(),		
	};	
	
	jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		    if(response.status == 'success'){	
					var html = '<div class="response"><div class="wpaas-notice notice '+response.status+'"><p><strong>Note: </strong>'+response.message+'</strong>.</p></div>';
					
				}else {
					var html = '<div class="response"><div class="wpaas-notice notice '+response.status+'"><p><strong>Note: </strong>'+response.message+'</strong>.</p></div>';				
			}
			$('.responsemsg').html(html);
			
		}
		
	
	});

	return false;
});