jQuery(document).ready(function($) {
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

			$('#em-organizer-image-buttons .em-spinner').fadeIn(300);
			$('#em_turn_off_image_button').attr('disabled', false);
			$('#em_upload_image_id').val(attachment.attributes.id);
			$('#em-organizer-image-preview img').attr('src', attachment.attributes.sizes.thumbnail.url).fadeIn(300);

			img.onload = function() {
				$('#em-organizer-image-buttons .em-spinner').fadeOut(300);
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
		$('#em-organizer-image-preview img').fadeOut(300, function() {
			$('#em-organizer-image-preview img').attr('src', '');
		});
	}
});