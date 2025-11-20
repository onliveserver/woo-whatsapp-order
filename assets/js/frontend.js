(function ($) {
	'use strict';

	console.log('=== WHATSAPP PLUGIN JS LOADED ===');
	console.log('jQuery version:', $.fn.jquery);
	console.log('Current page URL:', window.location.href);
	console.log('onliveWAOrder object check:', typeof onliveWAOrder !== 'undefined' ? 'EXISTS' : 'MISSING');

	/**
	 * Extract variation attributes from form
	 */
	function getVariations($form) {
		var attributes = {};
		$form.find('[name^="attribute_"]').each(function () {
			var $field = $(this);
			var name = $field.attr('name').replace('attribute_', '');
			var value = $field.val();
			if (value) {
				attributes[name] = value;
			}
		});
		return attributes;
	}

	/**
	 * Build AJAX request payload
	 */
	function getPayload($button) {
		var context = $button.data('context') || 'product';
		var payload = {
			action: 'vaog2jucg3f2',
			context: context
		};

		if (context === 'product') {
			var $form = $button.closest('form.cart');
			payload.product_id = $button.data('product') || ($form.length ? $form.find('input[name="product_id"]').val() : 0);
			payload.variation_id = $form.length ? $form.find('input[name="variation_id"]').val() : 0;
			payload.quantity = $form.length ? $form.find('input[name="quantity"]').val() : 1;
			payload.variations = JSON.stringify(getVariations($form));
		}

		return payload;
	}

	/**
	 * Handle button click
	 */
	function handleClick(event) {
		event.preventDefault();
		var $button = $(this);

		console.log('=== WHATSAPP BUTTON CLICK START ===');
		console.log('Timestamp:', new Date().toISOString());
		console.log('User logged in check:', typeof wp !== 'undefined' && wp.ajax && wp.ajax.settings && wp.ajax.settings.nonce ? 'YES' : 'UNKNOWN');
		console.log('Button element:', $button);
		console.log('Button data:', $button.data());
		console.log('onliveWAOrder object exists:', typeof onliveWAOrder !== 'undefined');
		if (typeof onliveWAOrder !== 'undefined') {
			console.log('onliveWAOrder.ajaxUrl:', onliveWAOrder.ajaxUrl);
			console.log('onliveWAOrder.nonce:', onliveWAOrder.nonce);
		} else {
			console.log('ERROR: onliveWAOrder object is undefined!');
			showError('JavaScript configuration error - onliveWAOrder not loaded');
			return;
		}

		// Show loading state
		$button.prop('disabled', true);
		var originalText = $button.html();
		$button.html('⏳ Loading...');

		var payload = getPayload($button);
		console.log('Generated payload:', payload);

		// Add nonce to payload
		payload.nonce = onliveWAOrder.nonce;
		console.log('Final AJAX payload:', payload);
		console.log('AJAX URL:', onliveWAOrder.ajaxUrl);

		// Send request
		console.log('=== SENDING AJAX REQUEST ===');
		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: payload,
			dataType: 'json',

			success: function (response) {
				console.log('=== AJAX SUCCESS RESPONSE RECEIVED ===');
				console.log('Raw response:', response);
				console.log('Response success flag:', response.success);

				if (response.success && response.data && response.data.url) {
					console.log('Opening WhatsApp URL:', response.data.url);
					window.open(response.data.url, '_blank');
				} else {
					console.log('Response indicates failure or missing URL');
					var errorMsg = response.message || 'An error occurred';
					if (response.data && response.data.debug) {
						console.log('Debug info from server:', response.data.debug);
						errorMsg += '\n\nDebug Info:\n' + JSON.stringify(response.data.debug, null, 2);
					}
					showError(errorMsg);
				}
			},

			error: function (xhr, status, error) {
				console.log('=== AJAX ERROR OCCURRED ===');
				console.log('XHR status:', xhr.status);
				console.log('Status text:', status);
				console.log('Error:', error);
				console.log('Response headers:', xhr.getAllResponseHeaders());

				var errorMsg = 'Request failed - please try again';
				if (xhr.responseText) {
					console.log('Raw response text:', xhr.responseText);
					try {
						var response = JSON.parse(xhr.responseText);
						console.log('Parsed error response:', response);
						if (response.data && response.data.debug) {
							console.log('Debug info from error response:', response.data.debug);
							errorMsg += '\n\nDebug Info:\n' + JSON.stringify(response.data.debug, null, 2);
						}
					} catch (e) {
						console.log('Failed to parse response as JSON:', e);
						errorMsg += '\n\nServer Response: ' + xhr.responseText;
					}
				} else {
					console.log('No response text received');
				}
				showError(errorMsg);
			},

			complete: function () {
				console.log('=== AJAX REQUEST COMPLETE ===');
				// Restore button
				$button.prop('disabled', false);
				$button.html(originalText);
			}
		});
	}

	/**
	 * Show error message to user
	 */
	function showError(message) {
		alert('WhatsApp Error: ' + message);
	}

	// Initialize on document ready
	$(document).ready(function () {
		console.log('=== DOCUMENT READY - ATTACHING WHATSAPP HANDLERS ===');
		console.log('Looking for buttons with class: .onlive-wa-order-button');
		var buttonCount = $('.onlive-wa-order-button').length;
		console.log('Found', buttonCount, 'WhatsApp buttons on page');

		if (buttonCount > 0) {
			console.log('Buttons found:', $('.onlive-wa-order-button'));
			$('.onlive-wa-order-button').each(function(index) {
				console.log('Button', index + 1, 'data:', $(this).data());
			});
		} else {
			console.log('WARNING: No WhatsApp buttons found on page!');
		}

		$(document).on('click', '.onlive-wa-order-button', handleClick);
		console.log('Click handler attached to .onlive-wa-order-button');
	});

})(jQuery);
