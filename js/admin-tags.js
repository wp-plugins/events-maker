jQuery(document).ready(function($) {
	
	// Google Map field
	if(typeof google !== 'undefined') {
		var emGoogleGeocoder = new google.maps.Geocoder();
		var emCoordinates = new google.maps.LatLng($('#event-google-map-latitude').val(), $('#event-google-map-longitude').val());
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
			$('#event-google-map-latitude').val(event.latLng.lat().toFixed(7));
			$('#event-google-map-longitude').val(event.latLng.lng().toFixed(7));
			emGoogleMap.setCenter(emGoogleMarker.position);
		});

		if($('#event-google-map').hasClass('event-minimap') === true) {
			emGoogleGeocoder.geocode({
				'address': emArgs.country
			}, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					emGoogleMarker.setPosition(results[0].geometry.location);
					$('#event-google-map-latitude').val(results[0].geometry.location.lat().toFixed(7));
					$('#event-google-map-longitude').val(results[0].geometry.location.lng().toFixed(7));
					emGoogleMap.setCenter(results[0].geometry.location);
				}
			});
		}

		$(document).on('change', 'input.google-map-input', function() {
			var emAddress = $('input#event-address').val();
			var emZip = $('input#event-zip').val();
			var emCity = $('input#event-city').val();
			var emState = $('input#event-state').val();
			var emCountry = $('input#event-country').val();

			emGoogleGeocoder.geocode({
				'address': (emAddress !== '' ? emAddress+', ' : '')+(emZip !== '' ? emZip : '')+(emCity !== '' ? emCity+', ' : '')+(emState !== '' ? emState+', ' : '')+(emCountry !== '' ? emCountry : '')
			}, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					emGoogleMarker.setPosition(results[0].geometry.location);
					$('#event-google-map-latitude').val(results[0].geometry.location.lat().toFixed(7));
					$('#event-google-map-longitude').val(results[0].geometry.location.lng().toFixed(7));
					emGoogleMap.setCenter(results[0].geometry.location);
				}
			});
		});
    }
	
	// Image field
	eventsMakerFileUpload = {
		frame: function() {
			if(this._frameEventsMaker)
				return this._frameEventsMaker;

			this._frameEventsMaker = wp.media({
				title: emArgs.title,
				frame: emArgs.frame,
				button: emArgs.button,
				multiple: emArgs.multiple,
				library: {
					type: 'image'
				}
			});

			this._frameEventsMaker.on('open', this.updateFrame).state('library').on('select', this.select);
			return this._frameEventsMaker;
		},
		select: function() {
			var attachment = this.frame.state().get('selection').first();
			var img = new Image();

			img.src = attachment.attributes.sizes.thumbnail.url;

			$('#em-tax-image-buttons .em-spinner').fadeIn(300);
			$('#em_turn_off_image_button').attr('disabled', false);
			$('#em_upload_image_id').val(attachment.attributes.id);
			$('#em-tax-image-preview img').attr('src', attachment.attributes.sizes.thumbnail.url).fadeIn(300);

			img.onload = function() {
				$('#em-tax-image-buttons .em-spinner').fadeOut(300);
			}
		},
		init: function() {
			$(document).on('click', 'input#em_upload_image_button', function(e) {
				e.preventDefault();
				eventsMakerFileUpload.frame().open();
			});
		}
	};

	eventsMakerFileUpload.init();

	$(document).on('click', '#em_turn_off_image_button', function(event) {
		emTurnOffRemoveButton();
	});

	$('#submit').click(function() {
		var emSubmit = $(this).closest('form');

		emSubmit.ajaxSuccess(function() {
			if(emSubmit.attr('id') === 'addtag') {
				emTurnOffRemoveButton();
			}
		});
	});

	function emTurnOffRemoveButton() {
		$('#em_turn_off_image_button').attr('disabled', true);
		$('#em_upload_image_id').val(0);
		$('#em-tax-image-preview img').fadeOut(300, function() {
			$('#em-tax-image-preview img').attr('src', '');
		});
	}
	
	$('#em-color-picker').wpColorPicker();
});