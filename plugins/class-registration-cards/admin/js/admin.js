jQuery(document).ready(function($) {

	// Initialize Color Picker
	if ( $('.crc-color-picker').length ) {
		$('.crc-color-picker').wpColorPicker();
	}

	// Media Uploader
	var crc_image_frame;
	$('.crc-upload-image').on('click', function(e) {
		e.preventDefault();
		if ( crc_image_frame ) {
			crc_image_frame.open();
			return;
		}
		crc_image_frame = wp.media({
			title: 'Select or Upload Class Image',
			button: { text: 'Use this image' },
			multiple: false
		});
		crc_image_frame.on('select', function() {
			var attachment = crc_image_frame.state().get('selection').first().toJSON();
			$('#crc_image_id').val(attachment.id);
			$('#crc_image_preview').html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;" />');
			$('.crc-remove-image').show();
		});
		crc_image_frame.open();
	});

	$('.crc-remove-image').on('click', function(e) {
		e.preventDefault();
		$('#crc_image_id').val('');
		$('#crc_image_preview').html('');
		$(this).hide();
	});

	// Badge Repeater
	var badgeIndex = $('.crc-badge-row').length;
	$('.crc-add-badge').on('click', function(e) {
		e.preventDefault();
		var tmpl = $('#tmpl-crc-badge').html();
		tmpl = tmpl.replace(/{{index}}/g, badgeIndex);
		$('#crc-badges-container').append(tmpl);
		badgeIndex++;
	});
	$(document).on('click', '.crc-remove-badge', function(e) {
		e.preventDefault();
		$(this).closest('.crc-badge-row').remove();
	});

	// Feature Repeater
	var featureIndex = $('.crc-feature-row').length;
	$('.crc-add-feature').on('click', function(e) {
		e.preventDefault();
		var tmpl = $('#tmpl-crc-feature').html();
		tmpl = tmpl.replace(/{{index}}/g, featureIndex);
		$('#crc-features-container').append(tmpl);
		featureIndex++;
	});
	$(document).on('click', '.crc-remove-feature', function(e) {
		e.preventDefault();
		$(this).closest('.crc-feature-row').remove();
	});

	// Drag and Drop Sortable for List Table
	if ( $('table.wp-list-table.class_registrations').length || $('table.wp-list-table').find('.crc-drag-handle').length ) {
		$('table.wp-list-table tbody').sortable({
			handle: '.crc-drag-handle',
			helper: function(e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});
				return ui;
			},
			update: function(event, ui) {
				var postIDs = [];
				$('table.wp-list-table tbody tr').each(function() {
					// Post ID is in id attribute like 'post-123'
					var idString = $(this).attr('id');
					if (idString) {
						postIDs.push(idString.replace('post-', ''));
					}
				});
				
				$.post(crc_ajax.url, {
					action: 'crc_update_post_order',
					order: postIDs
				}, function(response) {
					if (response.success) {
						// Success UI feedback if needed
						ui.item.css('background-color', '#dff0d8').animate({backgroundColor: 'transparent'}, 1000);
					}
				});
			}
		});
	}

});
