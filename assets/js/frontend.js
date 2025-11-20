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
			action: 'onlive_wa_build_message',
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
		if (typeof onliveWAOrder === 'undefined') {
			alert('Plugin configuration error');
			return;
		}

		event.preventDefault();
		var $button = $(this);

		// Show loading state
		$button.prop('disabled', true);
		var originalText = $button.html();
		$button.html('<span style="display:inline-block; margin-right:5px;">⏳</span>Loading...');

		// Build and send request
		$.ajax({
			type: 'POST',
			url: onliveWAOrder.ajaxUrl,
			data: getPayload($button),
			dataType: 'json',
			timeout: 10000,

			success: function (response) {
				// Check response structure
				if (!response || typeof response !== 'object') {
					showError('Invalid response from server');
					return;
				}

				// Success case
				if (response.success && response.data && response.data.url) {
					window.open(response.data.url, '_blank', 'noopener');
				} else {
					// Error case - use message from response
					var errorMsg = response.message || (response.data && response.data.message) || 'An error occurred';
					showError(errorMsg);
				}
			},

			error: function (xhr, status, error) {
				// Handle different error types
				if (status === 'timeout') {
					showError('Request timeout - please try again');
				} else if (xhr.status === 0) {
					showError('Network error - please check your connection');
				} else if (xhr.responseJSON && xhr.responseJSON.message) {
					showError(xhr.responseJSON.message);
				} else {
					showError('Request failed - please try again');
				}
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
