jQuery(document).ready(function($) {

	if(typeof google !== 'undefined') {
		var emGoogleGeocoder = new google.maps.Geocoder();
		var emCoordinates = new google.maps.LatLng($('#event-location-latitude').val(), $('#event-location-longitude').val());
        var emGoogleMap = new google.maps.Map(
			document.getElementById('event-google-map'), {
				disableDoubleClickZoom: false,
				draggable: true,
				keyboardShortcuts: true,
				scrollwheel: true,
				zoomControl: true,
				scaleControl: false,
				rotateControl: false,
				panControl: false,
				mapTypeControl: true,
				streetViewControl: true,
				zoom: 15,
				center: emCoordinates,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
		);

        emGoogleMarker = new google.maps.Marker({
            position: emCoordinates,
            map: emGoogleMap,
            draggable: true
        });

		google.maps.event.addListener(emGoogleMarker, 'dragend', function(event) {
			$('#event-location-latitude').val(event.latLng.lat().toFixed(7));
			$('#event-location-longitude').val(event.latLng.lng().toFixed(7));
			emGoogleMap.setCenter(emGoogleMarker.position);
		});

		if($('#event-google-map').hasClass('event-minimap') === true) {
			emGoogleGeocoder.geocode({
				'address': emArgs.country
			}, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					emGoogleMarker.setPosition(results[0].geometry.location);
					$('#event-location-latitude').val(results[0].geometry.location.lat().toFixed(7));
					$('#event-location-longitude').val(results[0].geometry.location.lng().toFixed(7));
					emGoogleMap.setCenter(results[0].geometry.location);
				}
			});
		}

		$(document).on('change', 'input.em-gm-input', function() {
			var emAddress = $('input#event-location-address').val();
			var emZip = $('input#event-location-zip').val();
			var emCity = $('input#event-location-city').val();
			var emState = $('input#event-location-state').val();
			var emCountry = $('input#event-location-country').val();

			emGoogleGeocoder.geocode({
				'address': (emAddress !== '' ? emAddress+', ' : '')+(emZip !== '' ? emZip : '')+(emCity !== '' ? emCity+', ' : '')+(emState !== '' ? emState+', ' : '')+(emCountry !== '' ? emCountry : '')
			}, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					emGoogleMarker.setPosition(results[0].geometry.location);
					$('#event-location-latitude').val(results[0].geometry.location.lat().toFixed(7));
					$('#event-location-longitude').val(results[0].geometry.location.lng().toFixed(7));
					emGoogleMap.setCenter(results[0].geometry.location);
				}
			});
		});
    }
});