(function($) {
	
	$(document).ready(function() {
	
		// Google Map field
		if(typeof google !== 'undefined') {
			var emGoogleGeocoder = new google.maps.Geocoder();
			var emCoordinates = new google.maps.LatLng($('#field-em-google_map #em-google_map-latitude').val(), $('#field-em-google_map #em-google_map-longitude').val());
	        var emGoogleMap = new google.maps.Map(
				document.getElementById('em-google_map'), {
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
				$('#field-em-google_map #em-google_map-latitude').val(event.latLng.lat().toFixed(7));
				$('#field-em-google_map #em-google_map-longitude').val(event.latLng.lng().toFixed(7));
				emGoogleMap.setCenter(emGoogleMarker.position);
			});
	
			if($('#em-google_map-latitude').val() == '0' || $('#em-google_map-longitude').val() == '0') {
				emGoogleGeocoder.geocode({
					'address': emArgs.country
				}, function(results, status) {
					
					if(status == google.maps.GeocoderStatus.OK) {
						emGoogleMarker.setPosition(results[0].geometry.location);
						$('#field-em-google_map #em-google_map-latitude').val(results[0].geometry.location.lat().toFixed(7));
						$('#field-em-google_map #em-google_map-longitude').val(results[0].geometry.location.lng().toFixed(7));
						emGoogleMap.setCenter(results[0].geometry.location);
					}
				});
			}
	
			$('#em-address, #em-zip, #em-city, #em-state, #em-country').on('change', function() {
				var emAddress = $('#em-address').val();
				var emZip = $('#em-zip').val();
				var emCity = $('#em-city').val();
				var emState = $('#em-state').val();
				var emCountry = $('#em-country option:selected').text()
				
				console.log($('#em-country option:selected').text());
	
				emGoogleGeocoder.geocode({
					'address': (emAddress !== '' ? emAddress+', ' : '')+(emZip !== '' ? emZip : '')+(emCity !== '' ? emCity+', ' : '')+(emState !== '' ? emState+', ' : '')+(emCountry !== '' ? emCountry : '')
				}, function(results, status) {
					if(status == google.maps.GeocoderStatus.OK) {
						emGoogleMarker.setPosition(results[0].geometry.location);
						$('#field-em-google_map #em-google_map-latitude').val(results[0].geometry.location.lat().toFixed(7));
						$('#field-em-google_map #em-google_map-longitude').val(results[0].geometry.location.lng().toFixed(7));
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
	
				$('#field-em-image .spinner').fadeIn(300);
				$('#field-em-image #em-image-remove').attr('disabled', false);
				$('#field-em-image #em-image').val(attachment.attributes.id);
				$('#field-em-image #em-image-preview img').attr('src', attachment.attributes.sizes.thumbnail.url).fadeIn(300);
	
				img.onload = function() {
					$('#field-em-image .spinner').fadeOut(300);
				}
			},
			init: function() {
				$(document).on('click', '#field-em-image #em-image-select', function(e) {
					e.preventDefault();
					eventsMakerFileUpload.frame().open();
				});
			}
		};
	
		eventsMakerFileUpload.init();
	
		$(document).on('click', '#field-em-image #em-image-remove', function(event) {
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
			$('#field-em-image #em-image-remove').attr('disabled', true);
			$('#field-em-image #em-image').val(0);
			$('#field-em-image #em-image-preview img').fadeOut(300, function() {
				$('#field-em-image #em-image-preview img').attr('src', '');
			});
		}
		
		$('#field-em-color input').wpColorPicker();
	
	});

})(jQuery);