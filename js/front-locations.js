jQuery(document).ready(function($) {

	$.fn.textWidth = function(element) {
		var self = $(this);
		var children = self.children();
		var calculator = $('<span style="display: inline-block;">');
		var width;

		children.wrap(calculator);
		width = children.parent().width();
		children.unwrap();

		return width;
	};

	if(typeof google !== 'undefined') {
		var markerZIndex = 9999;
		var emMapLocationsName = [];
		var emMapLocationsAddress = [];
		var emMapCoordinates = [];
		var emMapMarkers = [];
		var emMarkersLength = emMapArgs.markers.length;
		var emMapTypeId = google.maps.MapTypeId.ROADMAP;

		for(var i = 0; i < emMarkersLength; i++) {
			emMapCoordinates[i] = new google.maps.LatLng(emMapArgs.markers[i]['latitude'], emMapArgs.markers[i]['longitude']);
			emMapLocationsName[i] = '<strong>'+emMapArgs.markers[i]['name']+'</strong>';
			emMapLocationsAddress[i] = emMapArgs.markers[i]['address'];
			emMapLocationsAddress[i] = emMapLocationsAddress[i]+(emMapArgs.markers[i]['city'] !== '' ? (emMapLocationsAddress[i] !== '' ? ', ' : '')+emMapArgs.markers[i]['city'] : '');
			emMapLocationsAddress[i] = emMapLocationsAddress[i]+(emMapArgs.markers[i]['zip'] !== '' ? (emMapLocationsAddress[i] !== '' ? ', ' : '')+emMapArgs.markers[i]['zip'] : '')
			emMapLocationsAddress[i] = '<span>'+emMapLocationsAddress[i]+'</span>';
		}

		if(emMapArgs.mapTypeId === 'ROADMAP') {
			emMapTypeId = google.maps.MapTypeId.ROADMAP
		} else if(emMapArgs.mapTypeId === 'HYBRID') {
			emMapTypeId = google.maps.MapTypeId.HYBRID
		} else if(emMapArgs.mapTypeId === 'SATELLITE') {
			emMapTypeId = google.maps.MapTypeId.SATELLITE
		} else if(emMapArgs.mapTypeId === 'TERRAIN') {
			emMapTypeId = google.maps.MapTypeId.TERRAIN
		}

        var emGoogleMap = new google.maps.Map(
			document.getElementById('event-google-map'), {
				disableDoubleClickZoom: false,
				center: emMapCoordinates[0],
				draggable: (emMapArgs.draggable === '1' ? true : false),
				keyboardShortcuts: (emMapArgs.keyboardShortcuts === '1' ? true : false),
				scrollwheel: (emMapArgs.scrollwheel === '1' ? true : false),
				zoom: parseInt(emMapArgs.zoom),
				mapTypeId: emMapTypeId,
				zoomControl: (emMapArgs.zoomControl === '1' ? true : false),
				mapTypeControl: (emMapArgs.mapTypeControl === '1' ? true : false),
				streetViewControl: (emMapArgs.streetViewControl === '1' ? true : false),
				overviewMapControl: (emMapArgs.overviewMapControl === '1' ? true : false),
				panControl: (emMapArgs.panControl === '1' ? true : false),
				rotateControl: (emMapArgs.rotateControl === '1' ? true : false),
				scaleControl: (emMapArgs.scaleControl === '1' ? true : false)
			}
		);

		google.maps.event.addListener(emGoogleMap, 'dragstart', function() {
			$('.location-tooltip').fadeOut(300);
		});

		google.maps.event.addListener(emGoogleMap, 'click', function() {
			$('.location-tooltip').fadeOut(300);
		});

		google.maps.event.addListener(emGoogleMap, 'zoom_changed', function() {
			$('.location-tooltip').fadeOut(300);
		});

		for(var i = 0; i < emMarkersLength; i++) {
			emMapMarkers[i] = new google.maps.Marker({
				position: emMapCoordinates[i],
				map: emGoogleMap,
				draggable: false,
				clickable: true,
				flat: false
			});

			$('<div id="location-tooltip-'+i+'" class="location-tooltip"><span class="name">'+emMapLocationsName[i]+'</span><br /><span class="address">'+emMapLocationsAddress[i]+'</span></div>').appendTo('#event-google-map');

			with({number: i}) {
				google.maps.event.addListener(emMapMarkers[i], 'click', function() {
					var tooltip = $('#location-tooltip-'+number);

					if(tooltip.css('display') !== 'block') {
						var map = this.getMap();
						var position = map.getProjection().fromLatLngToPoint(this.position);
						var topRight = map.getProjection().fromLatLngToPoint(map.getBounds().getNorthEast());
						var bottomLeft = map.getProjection().fromLatLngToPoint(map.getBounds().getSouthWest()); 
						var scale = Math.pow(2, map.getZoom()); 

						tooltip.show();
						position = new google.maps.Point((position.x - bottomLeft.x) * scale, (position.y-topRight.y) * scale);

						var widthName = $('#location-tooltip-'+number+' span.name').textWidth();
						var widthAddress = $('#location-tooltip-'+number+' span.address').textWidth();
						var longerWidth = (widthName > widthAddress ? widthName : widthAddress) + 2;

						tooltip.hide();
						tooltip.css('width', longerWidth+'px');
						tooltip.css('left', parseInt(position.x) - parseInt(parseInt(tooltip.css('width')) / 2)+'px');
						tooltip.css('top', (parseInt(position.y) + 5)+'px');
						tooltip.css('z-index', markerZIndex++);
						tooltip.fadeIn(300);
					} else {
						tooltip.fadeOut(300);
					}
				});
			}
		}

		if(emMarkersLength > 1) {
			var bounds = new google.maps.LatLngBounds();

			for(var i = 0; i < emMarkersLength; i++) {
				bounds.extend(emMapCoordinates[i]);
			}

			emGoogleMap.fitBounds(bounds);
		}
    }
});