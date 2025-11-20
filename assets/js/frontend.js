(function ($) {
	'use strict';

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

		// Show loading state
		$button.prop('disabled', true);
		var originalText = $button.html();
		$button.html('⏳ Loading...');

		var payload = getPayload($button);

		// Add nonce to payload
		payload.nonce = onliveWAOrder.nonce;

		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: payload,
			dataType: 'json',

			success: function (response) {
				if (response.success && response.url) {
					// Redirect to WhatsApp URL
					window.location.href = response.url;
				} else if (response.success && response.data && response.data.url) {
					// Fallback for old response format
					window.location.href = response.data.url;
				} else {
					var errorMsg = response.message || 'An error occurred';
					showError(errorMsg);
				}
			},

			error: function (xhr, status, error) {
				var errorMsg = 'Request failed - please try again';
				if (xhr.responseText) {
					try {
						var response = JSON.parse(xhr.responseText);
						if (response.message) {
							errorMsg = response.message;
						}
					} catch (e) {
						// Response is not JSON, use default error message
					}
				}
				showError(errorMsg);
			},

			complete: function () {
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
		$(document).on('click', '.onlive-wa-order-button', handleClick);
	});

})(jQuery);
